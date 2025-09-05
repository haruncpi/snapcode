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
	 * Prepare code to run.
	 *
	 * @param string $code code.
	 *
	 * @return string
	 */
	public function prepare_code( $code ) {
		$tokens            = token_get_all( "<?php\n" . $code );
		$output            = '';
		$statements        = array();
		$current_statement = '';
		$in_function_chain = false;

		foreach ( $tokens as $token ) {
			if ( is_array( $token ) ) {
				$current_statement .= $token[1];
			} else {
				$current_statement .= $token;
			}

			// Detect function chaining with '->'.
			if ( '->' === $token ) {
				$in_function_chain = true;
			}

			// Complete a statement if it's not in a function chain and ends with a semicolon.
			if ( ! $in_function_chain && ';' === $token ) {
				$statements[]      = $current_statement;
				$current_statement = '';
			}

			// End of function chain on semicolon.
			if ( $in_function_chain && ';' === $token ) {
				$in_function_chain = false;
				$statements[]      = $current_statement;
				$current_statement = '';
			}
		}

		// Add any remaining partial statement.
		if ( ! empty( $current_statement ) ) {
			$statements[] = $current_statement;
		}

		// Insert 'return' before the last statement.
		if ( ! empty( $statements ) ) {
			$last_statement = array_pop( $statements );
			// Add return before last statement.
			$statements[] = '$__wp_snapcode = ' . trim( $last_statement );
		}

		// Recombine the statements.
		$output = implode( "\n", $statements );
		return str_replace( '<?php', '', $output );
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
		//phpcs:disabled
		set_error_handler(
			function( $errno, $errstr, $errfile, $errline ) {
				remove_filter( 'log_query_custom_data', $this->log_callback );
				echo "[Error] $errstr in $errfile:$errline\n";
				return true; // prevent PHP default error handling.
			}
		);

		ob_start();
		try {
			Dumper::dump( eval( $code ) );
			echo "\n";
		} catch ( \Throwable $e ) {
			echo '[Exception] ' . $e->getMessage() . "\n";
		} finally {
			remove_filter( 'log_query_custom_data', $this->log_callback );
		}
		//phpcs:enabled
		restore_error_handler();

		return ob_get_clean();
	}

	/**
	 * Get output.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function get_output() {
		if ( ! Helper::is_nonce_valid() ) {
			wp_send_json_error( 'Nonce verification fail' );
		}

		if ( ! Request::has( 'code' ) ) {
			wp_send_json_success( '' );
		}

		$code       = '';
		$output_str = '';

		$this->empty_tmp_files();

		$row_code = Request::get( 'code', '', 'sanitize_textarea_field' );
		if ( empty( $row_code ) ) {
			wp_send_json_success( '' );
		}

		if ( ! str_ends_with( $row_code, ';' ) ) {
			$row_code .= ';';
		}

		$code = $this->prepare_code( $row_code );

		$add_filter    = "add_filter( 'log_query_custom_data', '{$this->log_callback}', 10, 5 );";
		$remove_filter = "remove_filter( 'log_query_custom_data', '{$this->log_callback}' );";
		$return_stmt   = 'return $__wp_snapcode;';

		$final_code = $add_filter . $code . $remove_filter . $return_stmt;
		$output_str = $this->get_output_by_eval( $final_code );

		wp_send_json_success( $output_str );
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
