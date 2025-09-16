<?php
/**
 * Controller Class
 *
 * @package SnapCode
 * @author Harun <harun.cox@gmail.com>
 */

namespace SnapCode\Controllers;

use SnapCode\Classes\Dumper;
use SnapCode\Core\Request;
use SnapCode\Helper;

/**
 * Class TinkerController
 */
#[\AllowDynamicProperties]
class TinkerController {

	/**
	 * Define constant if not defined.
	 */
	public function __construct() {
		$this->psysh_path   = SNAPCODE_DIR . 'vendor/bin/psysh';
		$this->tmp_dir      = SNAPCODE_DIR . 'tmp';
		$this->tmp_file     = $this->tmp_dir . '/tmp.txt';
		$this->query_file   = $this->tmp_dir . '/query.json';
		$this->wp_load      = ABSPATH . 'wp-load.php';
		$this->log_callback = 'SnapCode\Controllers\TinkerController::log_wp_query';

		if ( ! defined( 'SAVEQUERIES' ) ) {
			define( 'SAVEQUERIES', true );
		}
	}

	/**
	 * Check if a PHP statement is safe to assign to a variable.
	 *
	 * @param string $statement statement.
	 *
	 * @return bool
	 */
	private function is_assignable( string $statement ): bool {
		// If ends with block close, function, or control keywords – not assignable.
		$non_assignable_patterns = array(
			'/^\s*(return|echo|exit|die|throw|if|while|for|foreach|switch|case|break|continue)\b/i',
			'/^\s*}/',  // just closing bracket.
			'/^\s*\)/', // closing parenthesis alone.
		);

		foreach ( $non_assignable_patterns as $pattern ) {
			if ( preg_match( $pattern, $statement ) ) {
				return false;
			}
		}

		// Otherwise assume assignable expression.
		return true;
	}

	/**
	 * Prepare code to run.
	 *
	 * @param string $code code.
	 *
	 * @return string
	 */
	public function prepare_code( $code ) {
		$variable_assigned = false;
		$code              = trim( $code );

		// Split into lines and get last non-empty line.
		$lines     = preg_split( '/\r\n|\r|\n/', $code );
		$last_line = '';
		while ( ! empty( $lines ) ) {
			$last_line = trim( array_pop( $lines ) );
			if ( '' !== $last_line ) {
				break;
			}
		}

		// Check if last line is assignable.
		if ( $this->is_assignable( $last_line ) ) {
			$last_line         = '$__wp_snapcode = ' . rtrim( $last_line, ';' ) . ';';
			$variable_assigned = true;
		}

		// Rebuild code.
		$lines[] = $last_line;
		$code    = implode( "\n", $lines );

		return array( $variable_assigned, str_replace( '<?php', '', $code ) );
	}


	/**
	 * Empty tmp files.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function empty_tmp_files() {
		if ( ! is_dir( $this->tmp_dir ) ) {
			mkdir( $this->tmp_dir, 0755, true );
		}

		file_put_contents( $this->query_file, '[]' );
		file_put_contents( $this->tmp_file, '' );
	}

	/**
	 * Prepare output.
	 *
	 * @since 1.0.0
	 *
	 * @param array $output output.
	 *
	 * @return string
	 */
	private function prepare_output( $output ) {
		if ( empty( $output ) ) {
			return '';
		}

		$output_str = '';
		foreach ( $output as $line ) {
			$line        = preg_replace( '/^(=\s)/i', '', $line );
			$line        = preg_replace( '/^\s\s/i', '', $line );
			$output_str .= $line . "\n";
		}
		return $output_str;
	}

	/**
	 * Get output by cmd.
	 *
	 * @since 1.0.0
	 *
	 * @param string $code code.
	 *
	 * @return string
	 */
	private function get_output_by_cmd( $code ) {
		$bootstrap = "require_once '$this->wp_load';";
		$php_path  = Helper::get_option( 'phpPath', 'php' );
		$cmd       = "cat '$this->tmp_file' | '{$php_path}' '$this->psysh_path'";
		$output    = array();

		$code = $bootstrap . $code;

		try {
			file_put_contents( $this->tmp_file, $code );
			exec( "{$cmd} 2>&1", $output, $error );
		} catch ( \Throwable $th ) {
			$output = array( $th->getMessage() );
		}

		if ( 0 !== $error ) {
			$output = array( $error );
		}

		return $this->prepare_output( $output );
	}

	/**
	 * Get output by eval.
	 *
	 * @since 1.0.0
	 *
	 * @param string $code code.
	 *
	 * @return string
	 */
	private function get_output_by_eval( $code ) {
		$has_error = false;
		//phpcs:disabled
		set_error_handler(
			function( $errno, $errstr, $errfile, $errline ) use ( &$has_error ) {
				$has_error = true;
				remove_filter( 'log_query_custom_data', $this->log_callback );
				echo "<span style=\"color: red;\">[Error] </span> {$errstr} in {$errfile}:{$errline}\n";
				return true; // prevent PHP default error handling.
			}
		);

		ob_start();
		try {
			Dumper::dump( eval( $code ) );
			echo "\n";
		} catch ( \Throwable $e ) {
			$has_error = true;
			echo '<span style="color: red;">[Exception] </span>' . $e->getMessage() . "\n";
		} finally {
			remove_filter( 'log_query_custom_data', $this->log_callback );
		}
		//phpcs:enabled
		restore_error_handler();

		return array( $has_error, ob_get_clean() );
	}

	/**
	 * Get output.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function get_output() {
		$response = array(
			'success' => false,
			'message' => null,
			'data'    => array(),
		);

		if ( ! Helper::is_nonce_valid() ) {
			$response['message'] = 'Nonce verification fail';
			wp_send_json( $response );
		}

		if ( ! Request::has( 'code' ) ) {
			wp_send_json( $response );
		}

		$output_str = '';

		$this->empty_tmp_files();

		$row_code = wp_unslash( $_POST['code'] ?? '' ); //phpcs:ignore
		if ( empty( $row_code ) ) {
			wp_send_json_success( '' );
		}

		if ( ! str_ends_with( $row_code, ';' ) ) {
			$row_code .= ';';
		}

		list( $variable_assigned, $code ) = $this->prepare_code( $row_code );

		try {
			$add_filter    = "add_filter( 'log_query_custom_data', '{$this->log_callback}', 10, 5 );";
			$remove_filter = "remove_filter( 'log_query_custom_data', '{$this->log_callback}' );";
			$return_stmt   = $variable_assigned ? 'return $__wp_snapcode;' : '';

			$final_code = $add_filter . $code . $remove_filter . $return_stmt;

			$start_time                     = microtime( true );
			list( $has_error, $output_str ) = $this->get_output_by_eval( $final_code );

			// make times are human readable and with unit.
			$end_time                = microtime( true );
			$response['performance'] = array(
				'execution_time' => number_format( ( $end_time - $start_time ) * 1000, 3 ) . ' ms',
			);

			$query_file = $this->query_file;
			if ( file_exists( $query_file ) ) {
				$queries    = json_decode( file_get_contents( $query_file ) );
				$query_time = 0;
				foreach ( $queries as $query ) {
					$query_time += $query->query_time;
				}

				$response['performance']['query_time'] = number_format( $query_time * 1000, 3 ) . ' ms';
				$response['queries']                   = is_array( $queries ) ? $queries : array();
			}

			if ( $has_error ) {
				$response['success'] = false;
				$response['message'] = $output_str;
			} else {
				$response['success'] = true;
				$response['data']    = $output_str;
			}
		} catch ( \Throwable $th ) {
			$response['success'] = false;
			$response['message'] = $th->getMessage();
		}

		wp_send_json( $response );
	}

	/**
	 * Log WP Query.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $query_data      Custom query data.
	 * @param string $query           The query's SQL.
	 * @param float  $query_time      Total time spent on the query, in seconds.
	 * @param string $query_callstack Comma-separated list of the calling functions.
	 * @param float  $query_start     Unix timestamp of the time at the start of the query.
	 *
	 * @return array
	 */
	public static function log_wp_query( $query_data, $query, $query_time, $query_callstack, $query_start ) {
		$file = SNAPCODE_DIR . '/tmp/query.json';

		$arr = array();
		if ( file_exists( $file ) ) {
			$arr = json_decode( file_get_contents( $file ) );
		}

		$arr[] = array(
			'query'      => trim( $query ),
			'query_time' => $query_time,
		);

		file_put_contents( $file, json_encode( $arr, JSON_PRETTY_PRINT ) );

		return $query_data;
	}

	/**
	 * Save Settings
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function save_settings() {
		if ( ! Helper::is_nonce_valid() ) {
			wp_send_json(
				array(
					'success' => false,
					'message' => 'Nonce verification fail',
				)
			);
		}

		$settings = json_decode( Request::get( 'settings', '' ), true );

		update_option( 'snapcode_settings', $settings );

		wp_send_json(
			array(
				'success' => true,
				'message' => 'Successfully saved',
			)
		);
	}
}
