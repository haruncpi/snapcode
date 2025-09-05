<?php
/**
 * Common header
 *
 * @since 1.0.0
 *
 * @package SnapCode
 * @author Harun <harun.cox@gmail.com>
 */

use SnapCode\Helper;

$php_path = Helper::get_option( 'phpPath', '/opt/homebrew/bin/php' );
?>

<style>
#wpcontent{padding: 0;}
.notice-error,.notice-warning{ display: none!important; }
.notice, [ng\:cloak], [ng-cloak], [data-ng-cloak], [x-ng-cloak], .ng-cloak, .x-ng-cloak {
		display: none !important;
}

#adminmenu{overflow-y: auto!important;max-height: calc(100vh - 75px)!important;}
#adminmenu::-webkit-scrollbar{width: 1px!important;}
</style>

<div ng-app="myApp" ng-controller="AppCtrl" class="wp-tinker-app" ng-cloak>
	<!-- global settings -->
	<div id="snapcode-settings" style="display: none;">
		<div class="snapcode-settings-content">
			<p><strong>PHP Path</strong></p>
			<input ng-model="settings.phpPath" name="php-path" ng-init="settings.phpPath='<?php echo esc_attr( $php_path ); ?>'" type="text">
			<br>
			<button ng-click="saveSettings(settings)" ng-disabled="saving" type="button" class="button button-primary btn-snapcode-save-settings">{{saving? 'Saving...':'Save'}}</button>
		</div>
	</div>
	<!-- end global settings -->

	<div class="wp-tinker-header">
		<div class="snapcode-title">
			<h2><span class="dashicons dashicons-editor-code"></span> SnapCode</h2>
		</div>

		<div class="wp-tinker-config">
			<div class="snapcode-icon-button" ng-click="toggleFullScreen()"><span class="icon-frame"></span> {{isFullScreen? 'Minimize':'Maximize'}}</div>
			<!-- <button ng-click="openSettings()" type="button" class="button button-default" style="line-height: 22px;"><span class="dashicons dashicons-admin-settings"></span> Settings</button> -->

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
