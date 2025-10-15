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
	<!-- snapcode info -->
	<div id="snapcode-info" style="display: none;">
		<p>
			<span class="icon-tag"></span> Version: <strong>v<?php echo esc_html( SNAPCODE_VERSION ); ?></strong><br>
			<span class="icon-user"></span> Author: <a target="_blank" href="https://github.com/haruncpi">Harun Ur Rashid</a><br>
		</p>

		<p class="snapcode-support-text"><span class="icon-heart"></span> Support this plugin by</p>
		<ul class="snapcode-support-list">
			<li><span class="icon-check"></span> Giving a GitHub star <a target="_blank" href="https://github.com/haruncpi/snapcode">here</a></li>
			<li><span class="icon-check"></span> Buy me a coffee <a target="_blank" href="https://www.buymeacoffee.com/haruncpi">here</a></li>
		</ul>
	</div>
	<!-- end snapcode info -->

	<div class="wp-tinker-header">
		<div class="snapcode-title">
			<h2><span class="dashicons dashicons-editor-code"></span> SnapCode</h2>
		</div>

		<div class="wp-tinker-config">
			<div class="snapcode-icon-button" ng-click="openInfo()"><span class="icon-info"></span> Info</div>
			<div class="snapcode-icon-button" ng-click="toggleScreenMode()"><span class="icon-directions"></span> {{screenMode==='horizontal'?'Vertical':'Horizontal'}}</div>
			<div class="snapcode-icon-button" ng-click="toggleFullScreen()"><span class="icon-frame"></span> {{isFullScreen? 'Minimize':'Maximize'}}</div>

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
