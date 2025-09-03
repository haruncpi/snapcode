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
		<div>
			<h2><span class="dashicons dashicons-editor-code"></span> SnapCode</h2>
		</div>
		<div class="wp-tinker-config">
			<div>
				<input type="hidden" name="_wpnonce" value="<?php echo esc_attr( wp_create_nonce( 'wp_snapcode' ) ); ?>">
				<span>PHP Path</span> 
				<input ng-model="phpPath" name="php-path" ng-init="phpPath='<?php echo esc_attr( $php_path ); ?>'" type="text">
				<button type="button" ng-click="saveConfig(phpPath)" class="button button-secondary" ng-disabled="saving">{{saving? 'Saving...':'Save'}}</button>
			</div>

			<!-- update info -->
			<div class="wp-tinker-update-info" ng-show="pluginInfo.updateAvailable">
				<span style="color: red;" ng-hide="updating">New version available - <strong>v{{pluginInfo.newVersion}}</strong></span>

				<button type="button" ng-hide="updating" class="button button-primary" ng-click="updatePlugin()">{{updating? 'Updating':'Update Now'}}</button>

				<span class="updating-info" ng-show="updating">
					<span class="dashicons dashicons-update"></span>	
					Updating version from <strong>{{pluginInfo.currentVersion}}</strong> to <strong>{{pluginInfo.newVersion}}</strong>
				</span>	
			</div>
			<!-- end update info -->

		</div>
	</div>
