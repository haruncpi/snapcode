<?php
/**
 * Common header
 *
 * @since 1.0.0
 *
 * @package SnapCode
 */

use SnapCode\Controllers\TinkerController;

$php_path = TinkerController::get_php_path();
?>

<style>
#wpcontent{padding: 0;}
.notice-error,.notice-warning{ display: none; }
</style>

<div ng-app="myApp" ng-controller="AppCtrl" class="wp-tinker-app">
	<div class="wp-tinker-header">
		<h2><span class="dashicons dashicons-editor-code"></span> SnapCode</h2>
		<div class="wp-tinker-config">
			<input type="hidden" name="_wpnonce_php_path" value="<?php echo esc_attr( wp_create_nonce( 'wp_tinker' ) ); ?>">
			<span>PHP Path</span> 
			<input ng-model="phpPath" name="php-path" ng-init="phpPath='<?php echo esc_attr( $php_path ); ?>'" type="text">
			<button type="button" ng-click="saveConfig(phpPath)" class="button button-secondary" >Save</button>
		</div>
	</div>
