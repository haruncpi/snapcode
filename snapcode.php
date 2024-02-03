<?php
/**
 * Plugin Name: SnapCode
 * Description: Run WordPress code instantly without a code editor
 * Author: haruncpi
 * Version: 1.0.0
 * Author URI: https://github.com/haruncpi
 * Requires PHP: 5.6
 * Requires at least: 5.3
 * Tested up to: 6.4
 * License: GPLv2 or later
 *
 * @package SnapCode
 */

use SnapCode\Plugin;

require_once 'vendor/autoload.php';
require_once 'constants.php';

Plugin::get_instance();
