<?php
/**
 * Admin Menu Register
 *
 * @package SnapCode
 * @author Harun <harun.cox@gmail.com>
 */

namespace SnapCode\Hooks;

use SnapCode\Core\BasePlugin;
use SnapCode\Core\Request;

/**
 * Class AdminMenu
 */
class AdminMenu extends BasePlugin {

	/**
	 * Register hooks
	 */
	public function __construct() {
		parent::__construct();
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_filter( 'admin_footer_text', array( $this, 'change_admin_footer_text' ), 999 );
	}

	/**
	 * Change admin footer text.
	 *
	 * @param string $text text.
	 *
	 * @return string
	 */
	public function change_admin_footer_text( $text ) {
		if ( 'snapcode' === Request::get( 'page' ) ) {
			return 'SnapCode - v' . SNAPCODE_VERSION;
		}

		return $text;
	}

	/**
	 * Admin menu for wp tinker.
	 *
	 * @return void
	 */
	public function add_admin_menu() {
		$admin_menu_text = 'SnapCode';
		$parent_slug     = 'snapcode';
		$position        = 80;

		add_menu_page(
			$admin_menu_text,
			$admin_menu_text,
			'manage_options',
			$parent_slug,
			array( $this, 'show_wp_tinker_page' ),
			'dashicons-editor-code',
			$position
		);
	}

	/**
	 * Show tinker page.
	 *
	 * @return void
	 */
	public function show_wp_tinker_page() {
		if ( Request::has( 'view' ) ) {
			$view      = Request::get( 'view' );
			$view_path = $this->view_dir . "/$view.php";
			if ( file_exists( $view_path ) ) {
				include_once $view_path;
			}

			return;
		}

		include_once $this->view_dir . '/index.php';
	}
}
