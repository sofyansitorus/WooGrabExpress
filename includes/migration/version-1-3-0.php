<?php
/**
 * The file that defines the data migration structure
 *
 * @link       https://github.com/sofyansitorus
 * @since      1.3
 *
 * @package    WooGrabExpress
 * @subpackage WooGrabExpress/includes/migration
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

return array(
	'version' => '1.3.0',
	'options' => array(
		'gmaps_api_key'     => 'api_key',
		'gmaps_api_mode'    => 'travel_mode',
		'gmaps_api_avoid'   => 'route_restrictions',
		'grabexpress_title' => 'title_same_day',
		'cost_per_km'       => 'per_km_cost_same_day',
		'min_distance'      => 'per_km_min_distance_same_day',
		'min_cost'          => 'min_cost_same_day',
		'max_cost'          => 'max_cost_same_day',
		'max_weight'        => 'max_weight_same_day',
		'max_width'         => 'max_width_same_day',
		'max_length'        => 'max_length_same_day',
		'max_height'        => 'max_height_same_day',
		'max_distance'      => 'max_distance_same_day',
		'multiple_drivers'  => 'multiple_drivers_same_day',
	),
);
