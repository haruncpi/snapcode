<?php
/**
 * Helper class
 *
 * @package SnapCode
 */

namespace SnapCode;

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
}
