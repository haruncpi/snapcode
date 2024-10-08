<?php
/**
 * Common header
 *
 * @since 1.0.0
 *
 * @package SnapCode
 * @author Harun <harun.cox@gmail.com>
 */

use SnapCode\Controllers\TinkerController;

$php_path = TinkerController::get_php_path();
?>

<style>
#wpcontent{padding: 0;}
.notice-error,.notice-warning{ display: none!important; }
.notice, [ng\:cloak], [ng-cloak], [data-ng-cloak], [x-ng-cloak], .ng-cloak, .x-ng-cloak {
		display: none !important;
}
</style>

<div ng-app="myApp" ng-controller="AppCtrl" class="wp-tinker-app" ng-cloak>
	<div class="wp-tinker-header">
		<h2><span class="dashicons dashicons-editor-code"></span> SnapCode</h2>
		<div class="wp-tinker-config">
			<input type="hidden" name="_wpnonce_php_path" value="<?php echo esc_attr( wp_create_nonce( 'wp_tinker' ) ); ?>">
			<span>PHP Path</span> 
			<input ng-model="phpPath" name="php-path" ng-init="phpPath='<?php echo esc_attr( $php_path ); ?>'" type="text">
			<button type="button" ng-click="saveConfig(phpPath)" class="button button-secondary" >Save</button>
		</div>
	</div>
