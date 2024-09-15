<?php
/**
 * Controller Class
 *
 * @package SnapCode
 * @author Harun <harun.cox@gmail.com>
 */

namespace SnapCode\Controllers;

use SnapCode\Core\Request;

/**
 * Class TinkerController
 */
class TinkerController {

	/**
	 * Define constant if not defined.
	 */
	public function __construct() {
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
		// Split into lines and get the last line.
		$lines     = explode( "\n", $code );
		$last_line = trim( array_pop( $lines ) );

		// Ensure the last line ends with a semicolon, then prepend 'return'.
		$last_line = 'return ' . rtrim( $last_line, ';' ) . ';';

		// Reassemble the code.
		return implode( "\n", $lines ) . "\n" . $last_line;
	}

	/**
	 * Get output.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function get_output() {

		$psysh_path   = SNAPCODE_DIR . 'vendor/bin/psysh';
		$psysh_config = SNAPCODE_DIR . '.psysh.php';
		$tmp_dir      = SNAPCODE_DIR . 'tmp';
		$tmp_file     = $tmp_dir . '/tmp.txt';
		$wp_load      = ABSPATH . 'wp-load.php';
		$query_file   = $tmp_dir . '/query.json';

		$output     = array();
		$output_str = '';
		$code       = '';

		if ( ! is_dir( $tmp_dir ) ) {
			mkdir( $tmp_dir, 0755, true );
		}

		file_put_contents( $query_file, '[]' );
		file_put_contents( $tmp_file, '' );

		if ( ! wp_verify_nonce( Request::get( '_wpnonce' ), 'wp_tinker' ) ) {
			wp_send_json_error( 'Nonce verification fail' );
		}

		if ( isset( $_POST['code'] ) ) {
			$code = Request::get( 'code', '', 'sanitize_textarea_field' );

			if ( ! empty( $code ) ) {
				if ( substr( trim( $code ), -1 ) !== ';' ) {
					$code .= ';';
				}

				$code = $this->add_return_stmt( $code );

				$output_str = '';
				$bootstrap  = "require_once '$wp_load';";
				$cmd        = "cat '$tmp_file' | '{$this->get_php_path()}' '$psysh_path'";

				$callback      = 'SnapCode\Controllers\TinkerController::log_wp_query';
				$add_filter    = "add_filter( 'log_query_custom_data',   '$callback', 10, 5 );";
				$remove_filter = "remove_filter('log_query_custom_data', '$callback', 10, 5 );";

				$file_content = $bootstrap . $add_filter . $code . $remove_filter;
				file_put_contents( $tmp_file, $file_content );

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

		wp_send_json_success( '' );
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
		if ( ! wp_verify_nonce( Request::get( '_wpnonce_php_path' ), 'wp_tinker' ) ) {
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
