<?php
/**
 * Base Plugin Class
 *
 * @package SnapCode
 * @author Harun <harun.cox@gmail.com>
 */

namespace SnapCode\Core;

/**
 * Class BasePlugin
 */
class BasePlugin {

	/**
	 * Main plugin file path.
	 *
	 * @var string
	 */
	protected $plugin_file;

	/**
	 * Plugin directory
	 *
	 * @var string
	 */
	protected $plugin_dir;

	/**
	 * Plugin version
	 *
	 * @var string
	 */
	protected $plugin_version;

	/**
	 * View file directory
	 *
	 * @var string
	 */
	protected $view_dir;

	/**
	 * Asset URL
	 *
	 * @var string
	 */
	protected $asset_url;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->plugin_version = SNAPCODE_VERSION;
		$this->plugin_file    = SNAPCODE_FILE;
		$this->plugin_dir     = SNAPCODE_DIR;
		$this->view_dir       = $this->plugin_dir . '/views';
		$this->asset_url      = SNAPCODE_URL . 'assets';
	}
}
