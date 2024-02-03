<?php
/**
 * Controller Class
 *
 * @package SnapCode
 */

namespace SnapCode\Controllers;

use SnapCode\Core\Request;

/**
 * Class TinkerController
 */
class TinkerController {
	/**
	 * Register hooks.
	 */
	public function __construct() {
		if ( ! defined( 'SAVEQUERIES' ) ) {
			define( 'SAVEQUERIES', true );
		}

		add_action( 'wp_ajax_wptinker_output', array( $this, 'get_output' ) );
		add_action( 'wp_ajax_wptinker_save_config', array( $this, 'save_config' ) );
	}

	/**
	 * Get output.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function get_output() {

		$psys_path  = SNAPCODE_DIR . 'vendor/bin/psysh';
		$tmp_dir    = SNAPCODE_DIR . 'tmp';
		$tmp_file   = $tmp_dir . '/tmp.txt';
		$wp_load    = ABSPATH . 'wp-load.php';
		$query_file = $tmp_dir . '/query.json';

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
			$code = Request::get( 'code', '' );

			if ( ! empty( $code ) ) {

				$other = "require_once '$wp_load';";

				$filter = "add_filter( 'log_query_custom_data', 'SnapCode\Controllers\TinkerController::log_wp_query',10,5);";

				$file_content = $other . $filter . $code;
				file_put_contents( $tmp_file, $file_content );

				$cmd = "cat '$tmp_file' | '{$this->get_php_path()}' '$psys_path'";

				$output_str = '';
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
