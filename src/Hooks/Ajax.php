<?php
/**
 * Handle Ajax Request
 *
 * @package SnapCode
 */

namespace SnapCode\Hooks;

use SnapCode\Controllers\TinkerController;

/**
 * Class Ajax
 */
class Ajax {
	/**
	 * Register hooks.
	 */
	public function __construct() {

		$tinker_ctrl = new TinkerController();
		add_action( 'wp_ajax_wptinker_output', array( $tinker_ctrl, 'get_output' ) );
		add_action( 'wp_ajax_wptinker_save_config', array( $tinker_ctrl, 'save_config' ) );
	}
}
