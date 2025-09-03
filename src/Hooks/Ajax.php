<?php
/**
 * Handle Ajax Request
 *
 * @package SnapCode
 * @author Harun <harun.cox@gmail.com>
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
		add_action( 'wp_ajax_snapcode_output', array( $tinker_ctrl, 'get_output' ) );
		add_action( 'wp_ajax_snapcode_save_settings', array( $tinker_ctrl, 'save_settings' ) );
	}
}
