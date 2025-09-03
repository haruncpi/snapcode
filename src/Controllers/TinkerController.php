<?php
/**
 * Controller Class
 *
 * @package SnapCode
 * @author Harun <harun.cox@gmail.com>
 */

namespace SnapCode\Controllers;

use SnapCode\Core\Request;
use SnapCode\Helper;
use SnapCode\Classes\SnapCodeShellOutput;

/**
 * Class TinkerController
 */
#[\AllowDynamicProperties]
class TinkerController {

	/**
	 * Define constant if not defined.
	 */
	public function __construct() {
		$this->psysh_path = SNAPCODE_DIR . 'vendor/bin/psysh';
		$this->tmp_dir    = SNAPCODE_DIR . 'tmp';
		$this->tmp_file   = $this->tmp_dir . '/tmp.txt';
		$this->query_file = $this->tmp_dir . '/query.json';
		$this->wp_load    = ABSPATH . 'wp-load.php';

		if ( ! defined( 'SAVEQUERIES' ) ) {
			define( 'SAVEQUERIES', true );
		}
	}

	/**
	 * Add return statement to last line if not exist.
	 *
	 * @param string $code code.
	 *
	 * @return string
	 */
	public function add_return_stmt( $code ) {
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
			$statements[] = 'return ' . trim( $last_statement );
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

		$output     = array();
		$output_str = '';
		$code       = '';

		$this->empty_tmp_files();

		$code = Request::get( 'code', '', 'sanitize_textarea_field' );

		if ( ! empty( $code ) ) {
			if ( substr( trim( $code ), -1 ) !== ';' ) {
				$code .= ';';
			}

			$code = $this->add_return_stmt( $code );

			$output_str = '';
			$bootstrap  = "require_once '$this->wp_load';";
			$cmd        = "cat '$this->tmp_file' | '{$this->get_php_path()}' '$this->psysh_path'";

			$callback      = 'SnapCode\Controllers\TinkerController::log_wp_query';
			$add_filter    = "add_filter( 'log_query_custom_data',   '$callback', 10, 5 );";
			$remove_filter = "remove_filter('log_query_custom_data', '$callback', 10, 5 );";

			$file_content = $bootstrap . $add_filter . $code . $remove_filter;
			file_put_contents( $this->tmp_file, $file_content );

			exec( "{$cmd} 2>&1", $output, $error );

			if ( 0 !== $error ) {
				$output   = array();
				$output[] = $error;
			}

			foreach ( $output as $line ) {
				$line        = preg_replace( '/^(=\s)/i', '', $line );
				$line        = preg_replace( '/^\s\s/i', '', $line );
				$output_str .= $line . "\n";
			}

			wp_send_json_success( $output_str );
		}
	}

	/**
	 * Get output test (for testing purpose)
	 *
	 * @return void
	 *
	 * @throws Exception Exception.
	 */
	public function get_output_test() {
		if ( ! Request::has( 'code' ) ) {
			wp_send_json_success( '' );
		}

		$this->empty_tmp_files();

		if ( ! Helper::is_nonce_valid() ) {
			wp_send_json_error( 'Nonce verification fail' );
		}

		$input = Request::get( 'code', '', 'sanitize_textarea_field' );

		try {
			$timer = microtime( true );
			$input = $this->add_return_stmt( $input );

			$callback      = 'SnapCode\Controllers\TinkerController::log_wp_query';
			$add_filter    = "add_filter( 'log_query_custom_data','$callback', 10, 5 );";
			$remove_filter = "remove_filter('log_query_custom_data', '$callback', 10, 5 );";

			$input = $add_filter . "\n" . $input;
			file_put_contents( $this->tmp_file, $input );

			$output = new SnapCodeShellOutput( \Psy\Output\ShellOutput::VERBOSITY_NORMAL, true );

			$config = new \Psy\Configuration( array( 'configDir' => WP_CONTENT_DIR ) );
			$config->setOutput( $output );

			$psysh = new \Psy\Shell( $config );
			$psysh->setOutput( $output );
			$psysh->addCode( $input );

			ob_start( array( $psysh, 'writeStdout' ), 1 );
			set_error_handler( array( $psysh, 'handleError' ) );
			$_ = eval( $psysh->onExecute( $psysh->flushCode() ?: \Psy\ExecutionClosure::NOOP_INPUT ) );//phpcs:ignore
			restore_error_handler();

			$psysh->setScopeVariables( get_defined_vars() );
			$psysh->writeReturnValue( $_ );

			ob_end_flush();

			if ( $output->exception ) {
				throw $output->exception;
			}

			$execution_time = microtime( true ) - $timer;
			if ( $execution_time < 1 ) {
				$readable_time = number_format( $execution_time * 1000, 2 ) . ' ms';
			} else {
				$readable_time = number_format( $execution_time, 2 ) . ' sec';
			}
		} catch ( \Exception $e ) {
			$output = $e->getMessage();
		}

		$final_output = preg_replace( '/\e\[[0-9;]*m/', '', $output->output );
		$final_output = preg_replace( '/^=\s/', '', $final_output );

		wp_send_json_success( $final_output );
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
	 * Get PHP path
	 *
	 * @return string
	 */
	public static function get_php_path() {
		return get_option( 'wptinker_php_path', '/opt/homebrew/bin/php' );
	}

	/**
	 * Save Config
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function save_config() {
		if ( ! Helper::is_nonce_valid() ) {
			wp_send_json(
				array(
					'success' => false,
					'message' => 'Nonce verification fail',
				)
			);
		}

		$path = Request::get( 'php_path', '' );

		update_option( 'wptinker_php_path', $path );

		wp_send_json(
			array(
				'success' => true,
				'message' => 'Successfully saved',
			)
		);
	}
}
