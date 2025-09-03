<?php
/**
 * Plugin Name: SnapCode
 * Description: Run WordPress code instantly without a code editor
 * Author: haruncpi
 * Version: 1.0.6
 * Author URI: https://github.com/haruncpi
 * Requires PHP: 7.4
 * Requires at least: 5.3
 * Tested up to: 6.4
 * License: GPLv2 or later
 *
 * @package SnapCode
 */

use SnapCode\Plugin;

require_once __DIR__ . '/vendor/autoload.php';

define( 'SNAPCODE_VERSION', '1.0.6' );
define( 'SNAPCODE_FILE', __DIR__ . '/snapcode.php' );
define( 'SNAPCODE_DIR', plugin_dir_path( __FILE__ ) );
define( 'SNAPCODE_URL', plugin_dir_url( __FILE__ ) );
define( 'SNAPCODE_UPDATE_URL', 'https://raw.githubusercontent.com/haruncpi/snapcode/master/src/Updater/plugin.json' );

Plugin::get_instance();
