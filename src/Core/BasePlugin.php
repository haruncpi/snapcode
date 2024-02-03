<?php
/**
 * Base Plugin Class
 *
 * @package SnapCode
 */

namespace SnapCode\Core;

/**
 * Class BasePlugin
 */
class BasePlugin {
    //phpcs:disable
	protected $plugin_dir;
	protected $plugin_version;
	protected $view_dir;
	protected $admin_asset_url;
    //phpcs:enable

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->plugin_version  = SNAPCODE_VERSION;
		$this->plugin_dir      = SNAPCODE_DIR;
		$this->view_dir        = $this->plugin_dir . '/src/Views';
		$this->admin_asset_url = SNAPCODE_URL . 'assets/admin';
	}
}
