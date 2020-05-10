<?php
/**
 * The file that defines the services plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://github.com/sofyansitorus
 *
 * @package    WooGrabExpress
 * @subpackage WooGrabExpress/includes
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The services plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @package    WooGrabExpress
 * @subpackage WooGrabExpress/includes
 * @author     Sofyan Sitorus <sofyansitorus@gmail.com>
 */
class WooGrabExpress_Migration_1_3_0 extends WooGrabExpress_Migration {

	/**
	 * Plugin migration data version
	 *
	 * @return string
	 */
	public static function get_version() {
		return '1.3.0';
	}

	/**
	 * Get migration options data to update
	 *
	 * @return array
	 */
	public function get_update_options() {
		$gmaps_api_key   = isset( $this->wc_shipping->instance_settings['gmaps_api_key'] ) ? $this->wc_shipping->instance_settings['gmaps_api_key'] : '';
		$gmaps_api_mode  = isset( $this->wc_shipping->instance_settings['gmaps_api_mode'] ) ? $this->wc_shipping->instance_settings['gmaps_api_mode'] : 'driving';
		$gmaps_api_avoid = isset( $this->wc_shipping->instance_settings['gmaps_api_avoid'] ) ? $this->wc_shipping->instance_settings['gmaps_api_avoid'] : '';

		return array(
			'api_key'            => $gmaps_api_key,
			'travel_mode'        => $gmaps_api_mode,
			'route_restrictions' => $gmaps_api_avoid,
		);
	}

	/**
	 * Get migration options data to delete
	 *
	 * @return array
	 */
	public function get_delete_options() {
		return array(
			'gmaps_api_key',
			'gmaps_api_mode',
			'gmaps_api_avoid',
		);
	}
}
