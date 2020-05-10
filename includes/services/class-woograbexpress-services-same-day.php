<?php
/**
 * The file that defines the core plugin class
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
 * The core plugin class.
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
class WooGrabExpress_Services_Same_Day extends WooGrabExpress_Services {

	/**
	 * Default service settings data
	 *
	 * @var array
	 */
	protected $default_settings = array(
		'enable'              => 'yes',
		'title'               => '',
		'min_cost'            => 15000,
		'max_cost'            => 0,
		'per_km_cost'         => 2500,
		'per_km_min_distance' => 0,
		'max_weight'          => 5,
		'max_width'           => 25,
		'max_length'          => 32,
		'max_height'          => 12,
		'max_distance'        => 40,
		'multiple_drivers'    => 'no',
	);

	/**
	 * Get service slug ID
	 *
	 * @return string
	 */
	public function get_slug() {
		return 'same_day';
	}

	/**
	 * Get service label
	 *
	 * @return string
	 */
	public function get_label() {
		return 'GrabExpress Same Day';
	}
}
