<?php
/**
 * Manage Plugin Update
 *
 * @package SnapCode
 * @author Harun <harun.cox@gmail.com>
 */

namespace SnapCode\Updater;

/**
 * Class Updater
 */
class Updater {
	/**
	 * Plugin slug
	 *
	 * @var string
	 */
	public $plugin_slug;

	/**
	 * Plugin version
	 *
	 * @var string
	 */
	public $version;

	/**
	 * Request cache key.
	 *
	 * @var string
	 */
	public $cache_key;

	/**
	 * Cache is allowed or not.
	 *
	 * @var bool
	 */
	public $cache_allowed;

	/**
	 * Update URL
	 *
	 * @see example https://rudrastyh.com/wp-content/uploads/updater/info.json
	 * @var string
	 */
	private $update_url = 'https://raw.githubusercontent.com/haruncpi/snapcode/master/src/Updater/plugin.json';


	/**
	 * Register hooks.
	 */
	public function __construct() {
		$this->plugin_slug   = plugin_basename( SNAPCODE_FILE );
		$this->version       = SNAPCODE_VERSION;
		$this->cache_key     = 'snapcode_update';
		$this->cache_allowed = true;

		add_filter( 'plugins_api', array( $this, 'plugin_info' ), 10, 3 );
		add_filter( 'site_transient_update_plugins', array( $this, 'check_update' ) );
	}

	/**
	 * Request
	 *
	 * @return mixed
	 */
	public function request() {

		$remote = get_transient( $this->cache_key );

		if ( false === $remote || ! $this->cache_allowed ) {

			$remote = wp_remote_get(
				$this->update_url,
				array(
					'timeout' => 5,
					'headers' => array(
						'Accept' => 'application/json',
					),
				)
			);

			if (
				is_wp_error( $remote )
				|| 200 !== wp_remote_retrieve_response_code( $remote )
				|| empty( wp_remote_retrieve_body( $remote ) )
			) {
				return false;
			}

			set_transient( $this->cache_key, $remote, HOUR_IN_SECONDS );

		}

		$remote = json_decode( wp_remote_retrieve_body( $remote ) );

		return $remote;

	}

	/**
	 * Plugin info
	 *
	 * @param false|object|array $res The result object or array. Default false.
	 * @param string             $action The type of information being requested from the Plugin Installation API.
	 * @param object             $args   Plugin API arguments.
	 *
	 * @return mixed
	 */
	public function plugin_info( $res, $action, $args ) {
		// do nothing if you're not getting plugin information right now.
		if ( 'plugin_information' !== $action ) {
			return $res;
		}

		// do nothing if it is not our plugin.
		if ( ! isset( $args->slug ) || $this->plugin_slug !== $args->slug ) {
			return $res;
		}

		// get updates.
		$remote = $this->request();

		if ( ! $remote ) {
			return $res;
		}

		$res = new \stdClass();

		$res->name           = $remote->name;
		$res->slug           = $remote->slug;
		$res->version        = $remote->version;
		$res->tested         = $remote->tested;
		$res->requires       = $remote->requires;
		$res->author         = $remote->author;
		$res->author_profile = $remote->author_profile;
		$res->download_link  = $remote->download_url;
		$res->trunk          = $remote->download_url;
		$res->requires_php   = $remote->requires_php;
		$res->last_updated   = $remote->last_updated;

		if ( ! empty( $remote->sections ) ) {
			$res->sections = array(
				'description'  => $remote->sections->description,
				'installation' => $remote->sections->installation,
				'changelog'    => $remote->sections->changelog,
			);
		}

		if ( ! empty( $remote->banners ) ) {
			$res->banners = array(
				'low'  => $remote->banners->low,
				'high' => $remote->banners->high,
			);
		}

		return $res;

	}

	/**
	 * Check Update
	 *
	 * @param mixed|object|array $transient The value of the 'update_plugins' site transient.
	 *
	 * @return mixed
	 */
	public function check_update( $transient ) {

		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		$remote = $this->request();

		if ( $remote
			&& isset( $remote->version )
			&& version_compare( $this->version, $remote->version, '<' )
		) {
			$res              = new \stdClass();
			$res->slug        = $this->plugin_slug;
			$res->plugin      = $this->plugin_slug;
			$res->new_version = $remote->version;
			$res->tested      = $remote->tested;
			$res->package     = $remote->download_url;

			$transient->response[ $res->plugin ] = $res;

		}

		return $transient;

	}
}
