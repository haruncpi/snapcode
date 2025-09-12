<?php
/**
 * Assets
 *
 * @package SnapCode
 * @author Harun <harun.cox@gmail.com>
 */

namespace SnapCode\Hooks;

use SnapCode\Core\BasePlugin;
use SnapCode\Core\Request;

/**
 * Class Assets
 */
class Assets extends BasePlugin {
	/**
	 * Register hooks
	 */
	public function __construct() {
		parent::__construct();
		add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_assets' ) );
	}

	/**
	 * Load assets
	 *
	 * @return void
	 */
	public function load_admin_assets() {
		if ( 'snapcode' !== Request::get( 'page' ) ) {
			return;
		}

		wp_enqueue_script( 'thickbox' );
		wp_enqueue_style( 'thickbox' );

		wp_enqueue_style( 'snapcode-simple-line-icons', 'https://cdnjs.cloudflare.com/ajax/libs/simple-line-icons/2.5.5/css/simple-line-icons.min.css', array(), $this->plugin_version );
		wp_enqueue_style( 'snapcode-admin-css', $this->asset_url . '/admin/css/style.css', array(), $this->plugin_version );
		wp_enqueue_script( 'snapcode-ace', $this->asset_url . '/admin/libs/ace/src-min/ace.js', array(), $this->plugin_version, true );
		wp_enqueue_script( 'snapcode-ace-beautify', $this->asset_url . '/admin/libs/ace/src-min/ext-beautify.js', array( 'snapcode-ace' ), $this->plugin_version, true );
		wp_enqueue_script( 'snapcode-ace-themelist', $this->asset_url . '/admin/libs/ace/src-min/ext-themelist.js', array( 'snapcode-ace' ), $this->plugin_version, true );
		wp_enqueue_script( 'snapcode-angularjs', $this->asset_url . '/admin/libs/angular.min.js', array(), $this->plugin_version, true );
		wp_enqueue_script( 'snapcode-adminjs', $this->asset_url . '/admin/js/angular-app.js', array( 'snapcode-angularjs' ), $this->plugin_version, true );

		wp_localize_script(
			'snapcode-adminjs',
			'_snapcode',
			array(
				'ajaxUrl'           => admin_url( 'admin-ajax.php' ),
				'pluginUrl'         => plugin_dir_url( SNAPCODE_FILE ),
				'version'           => $this->plugin_version,
				'updateUrl'         => SNAPCODE_UPDATE_URL,
				'pluginUpdateNonce' => wp_create_nonce( 'updates' ),
				'nonceKey'          => 'wp_snapcode',
				'nonceValue'        => wp_create_nonce( 'wp_snapcode' ),
			)
		);
	}
}
