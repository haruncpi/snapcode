<?php
/**
 * Code output and query view file.
 *
 * @since 1.0.0
 *
 * @package SnapCode
 * @author Harun <harun.cox@gmail.com>
 */

defined( 'ABSPATH' ) || exit;
?>

<?php require_once 'header.php'; ?>
	<div class="wptinker-wrapper">
		<input type="hidden" name="_wpnonce" value="<?php echo esc_attr( wp_create_nonce( 'wp_snapcode' ) ); ?>">

		<div class="input">
			<div class="input-header">
				<div>Write Code</div>
				<button class="button button-primary btn-snapcode-run" 
				ng-disabled="processing || !model.code"
				type="button" ng-click="getOutput()">{{processing? 'Running...':'► Run'}}</button>
			</div>
			<div id="editor"></div>


			<select id="theme-selector"
						ng-change="changeTheme(model.theme)" 
						ng-model="model.theme" ng-options="row.theme as row.caption for row in model.themes"></select>

		</div>

		<div class="output">
			<div class="output-header">
				<div class="tabs" ng-show="output">
					<div ng-click="setTab('output')" ng-class="tab==='output'?'active':''">Output</div>
					<div ng-show="queries.length" ng-class="tab==='sql'?'active':''" ng-click="setTab('sql')">SQL ({{queries.length}})</div>
				</div>

				<div class="snapcode-performance">
					<span ng-if="tab==='output' && outputResponse.performance.execution_time">Time: {{outputResponse.performance.execution_time}} | Memory: {{outputResponse.performance.memory_usage}}</span>
					<span ng-if="tab==='sql'">Query Time: {{outputResponse.performance.query_time}}</span>
				</div>
			</div>

			<pre class="output-code" ng-if="!outputResponse.success" ng-show="tab==='output'" ng-bind-html="output"></pre>
			<pre class="output-code" ng-if="outputResponse.success" ng-show="tab==='output'" ng-bind="output"></pre>

			<!-- SQL query tab -->
			<div class="output-sql" ng-show="tab=== 'sql' && queries.length">
				<div class="sql-query-wrapper" ng-repeat="row in queries track by $index">
					<p class="query-info">
						<span class="query-time">{{row.query_time*1000|number:3}} ms</span>
						<span class="query-copy" ng-click="copy(row.query,$event)">Copy</span>
					</p>
					<pre class="sql-query">{{prettySql(row.query)}}</pre>
				</div>
			</div>
			<!-- SQL query tab end -->
		</div>
	</div>

</div>
<!-- ng app close -->

