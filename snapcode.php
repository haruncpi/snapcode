<?php
/**
 * Plugin Name: SnapCode
 * Description: Run WordPress code instantly without a code editor
 * Author: haruncpi
 * Version: 1.0.2
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
require_once __DIR__ . '/constants.php';

Plugin::get_instance();
