<?php
/**
 * Main Plugin Class
 *
 * @package SnapCode
 */

namespace SnapCode;

use SnapCode\Hooks\AdminMenu;
use SnapCode\Hooks\Ajax;
use SnapCode\Hooks\Assets;
use SnapCode\Updater\Updater;

/**
 * Class Plugin
 */
class Plugin {
	/**
	 * Plugin instance
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Prevent to create instance.
	 */
	private function __construct(){}

	/**
	 * Get plugin instance
	 *
	 * @return self
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
			self::$instance->bootstrap();
		}

		return self::$instance;
	}

	/**
	 * Load plugin required classes.
	 *
	 * @return void
	 */
	private function bootstrap() {
		new AdminMenu();
		new Assets();
		new Ajax();
		// new Updater();
	}
}
