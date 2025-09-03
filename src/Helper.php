<?php
/**
 * Helper class
 *
 * @package SnapCode
 */

namespace SnapCode;

use SnapCode\Core\Request;

/**
 * Class Helper
 *
 * @package SnapCode
 */
class Helper {
	/**
	 * Get plugin version
	 *
	 * @return string
	 */
	public static function get_version() {
		return SNAPCODE_VERSION;
	}

	/**
	 * Get php path
	 *
	 * @return string
	 */
	public static function get_php_path() {
		return trim( shell_exec( 'which php' ) );
	}

	/**
	 * Check nonce
	 *
	 * @return bool
	 */
	public static function is_nonce_valid() {
		return wp_verify_nonce( Request::get( 'wp_snapcode' ), 'wp_snapcode' );
	}
}
