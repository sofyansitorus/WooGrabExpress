<?php
/**
 * Helpers file
 *
 * @link       https://github.com/sofyansitorus
 *
 * @package    WooGrabExpress
 * @subpackage WooGrabExpress/includes
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Check if plugin is active
 *
 * @param string $plugin_file Plugin file name.
 */
function woograbexpress_is_plugin_active( $plugin_file ) {
	$active_plugins = (array) apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

	if ( is_multisite() ) {
		$active_plugins = array_merge( $active_plugins, (array) get_site_option( 'active_sitewide_plugins', array() ) );
	}

	return in_array( $plugin_file, $active_plugins, true ) || array_key_exists( $plugin_file, $active_plugins );
}

/**
 * Get i18n strings
 *
 * @param string $key Strings key.
 * @param string $default Default value.
 * @return mixed
 */
function woograbexpress_i18n( $key = '', $default = '' ) {
	$i18n = array(
		'drag_marker'  => __( 'Drag this marker or search your address at the input above.', 'woograbexpress' ),
		// translators: %s = distance unit.
		'per_unit'     => __( 'Per %s', 'woograbexpress' ),
		'map_is_error' => __( 'Map is error', 'woograbexpress' ),
		'latitude'     => __( 'Latitude', 'woograbexpress' ),
		'longitude'    => __( 'Longitude', 'woograbexpress' ),
		'buttons'      => array(
			'Get API Key'           => __( 'Get API Key', 'woograbexpress' ),
			'Back'                  => __( 'Back', 'woograbexpress' ),
			'Cancel'                => __( 'Cancel', 'woograbexpress' ),
			'Apply Changes'         => __( 'Apply Changes', 'woograbexpress' ),
			'Confirm Delete'        => __( 'Confirm Delete', 'woograbexpress' ),
			'Delete Selected Rates' => __( 'Delete Selected Rates', 'woograbexpress' ),
			'Add New Rate'          => __( 'Add New Rate', 'woograbexpress' ),
			'Save Changes'          => __( 'Save Changes', 'woograbexpress' ),
		),
		'errors'       => array(
			// translators: %s = Field name.
			'field_required'        => __( '%s field is required', 'woograbexpress' ),
			// translators: %1$s = Field name, %2$d = Minimum field value rule.
			'field_min_value'       => __( '%1$s field value cannot be lower than %2$d', 'woograbexpress' ),
			// translators: %1$s = Field name, %2$d = Maximum field value rule.
			'field_max_value'       => __( '%1$s field value cannot be greater than %2$d', 'woograbexpress' ),
			// translators: %s = Field name.
			'field_numeric'         => __( '%s field value must be numeric', 'woograbexpress' ),
			// translators: %s = Field name.
			'field_numeric_decimal' => __( '%s field value must be numeric and decimal', 'woograbexpress' ),
			// translators: %s = Field name.
			'field_select'          => __( '%s field value selected is not exists', 'woograbexpress' ),
			// translators: %1$d = row number, %2$s = error message.
			'table_rate_row'        => __( 'Table rate row #%1$d: %2$s', 'woograbexpress' ),
			// translators: %1$d = row number, %2$s = error message.
			'duplicate_rate_row'    => __( 'Shipping rules combination duplicate with rate row #%1$d: %2$s', 'woograbexpress' ),
			'finish_editing_api'    => __( 'Please finish the API Key Editing first!', 'woograbexpress' ),
			'table_rates_invalid'   => __( 'Table rates data is incomplete or invalid!', 'woograbexpress' ),
			'api_key_empty'         => __( 'Distance Calculator API Key cannot be empty!', 'woograbexpress' ),
			'api_key_picker_empty'  => __( 'Location Picker API Key cannot be empty!', 'woograbexpress' ),
		),
		'Save Changes' => __( 'Save Changes', 'woograbexpress' ),
		'Add New Rate' => __( 'Add New Rate', 'woograbexpress' ),
	);

	if ( ! empty( $key ) && is_string( $key ) ) {
		$keys = explode( '.', $key );

		$temp = $i18n;
		foreach ( $keys as $path ) {
			$temp = &$temp[ $path ];
		}

		return is_null( $temp ) ? $default : $temp;
	}

	return $i18n;
}

/**
 * Get shipping method instances
 *
 * @param bool $enabled_only Filter to includes only enabled instances.
 * @return array
 */
function woograbexpress_instances( $enabled_only = true ) {
	$instances = array();

	$zone_data_store = new WC_Shipping_Zone_Data_Store();

	$shipping_methods = $zone_data_store->get_methods( '0', $enabled_only );

	if ( $shipping_methods ) {
		foreach ( $shipping_methods as $shipping_method ) {
			if ( WOOGRABEXPRESS_METHOD_ID !== $shipping_method->method_id ) {
				continue;
			}

			$instances[] = array(
				'zone_id'     => 0,
				'method_id'   => $shipping_method->method_id,
				'instance_id' => $shipping_method->instance_id,
			);
		}
	}

	$zones = WC_Shipping_Zones::get_zones();

	if ( ! empty( $zones ) ) {
		foreach ( $zones as $zone ) {
			$shipping_methods = $zone_data_store->get_methods( $zone['id'], $enabled_only );
			if ( $shipping_methods ) {
				foreach ( $shipping_methods as $shipping_method ) {
					if ( WOOGRABEXPRESS_METHOD_ID !== $shipping_method->method_id ) {
						continue;
					}

					$instances[] = array(
						'zone_id'     => 0,
						'method_id'   => $shipping_method->method_id,
						'instance_id' => $shipping_method->instance_id,
					);
				}
			}
		}
	}

	return apply_filters( 'woograbexpress_instances', $instances );
}

/**
 * Inserts a new key/value before the key in the array.
 *
 * @param string $before_key The key to insert before.
 * @param array  $array An array to insert in to.
 * @param string $new_key The new key to insert.
 * @param mixed  $new_value The new value to insert.
 *
 * @return array
 */
function woograbexpress_array_insert_before( $before_key, $array, $new_key, $new_value ) {
	if ( ! array_key_exists( $before_key, $array ) ) {
		return $array;
	}

	$new = array();

	foreach ( $array as $k => $value ) {
		if ( $k === $before_key ) {
			$new[ $new_key ] = $new_value;
		}

		$new[ $k ] = $value;
	}

	return $new;
}

/**
 * Inserts a new key/value after the key in the array.
 *
 * @param string $after_key The key to insert after.
 * @param array  $array An array to insert in to.
 * @param string $new_key The new key to insert.
 * @param mixed  $new_value The new value to insert.
 *
 * @return array
 */
function woograbexpress_array_insert_after( $after_key, $array, $new_key, $new_value ) {
	if ( ! array_key_exists( $after_key, $array ) ) {
		return $array;
	}

	$new = array();

	foreach ( $array as $k => $value ) {
		$new[ $k ] = $value;

		if ( $k === $after_key ) {
			$new[ $new_key ] = $new_value;
		}
	}

	return $new;
}

/**
 * Check is in development environment.
 *
 * @return bool
 */
function woograbexpress_is_dev_env() {
	if ( defined( 'WOOGRABEXPRESS_DEV' ) && WOOGRABEXPRESS_DEV ) {
		return true;
	}

	if ( function_exists( 'getenv' ) && getenv( 'WOOGRABEXPRESS_DEV' ) ) {
		return true;
	}

	return false;
}

if ( ! function_exists( 'woograbexpress_autoload' ) ) :
	/**
	 * Class autoload
	 *
	 * @param string $class Class name.
	 *
	 * @return void
	 */
	function woograbexpress_autoload( $class ) {
		$class = strtolower( $class );

		if ( strpos( $class, 'woograbexpress' ) !== 0 ) {
			return;
		}

		if ( strpos( $class, 'woograbexpress_services_' ) === 0 ) {
			require_once WOOGRABEXPRESS_PATH . 'includes/services/class-' . str_replace( '_', '-', $class ) . '.php';
		} elseif ( strpos( $class, 'woograbexpress_migration_' ) === 0 ) {
			require_once WOOGRABEXPRESS_PATH . 'includes/migrations/class-' . str_replace( '_', '-', $class ) . '.php';
		} else {
			require_once WOOGRABEXPRESS_PATH . 'includes/classes/class-' . str_replace( '_', '-', $class ) . '.php';
		}
	}
endif;

if ( ! function_exists( 'woograbexpress_is_calc_shipping' ) ) :
	/**
	 * Check if current request is shipping calculator form.
	 *
	 * @return bool
	 */
	function woograbexpress_is_calc_shipping() {
		$field  = 'woocommerce-shipping-calculator-nonce';
		$action = 'woocommerce-shipping-calculator';

		if ( isset( $_POST['calc_shipping'], $_POST[ $field ] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ $field ] ) ), $action ) ) {
			return true;
		}

		return false;
	}
endif;

if ( ! function_exists( 'woograbexpress_calc_shipping_field_value' ) ) :
	/**
	 * Get calculated shipping for fields value.
	 *
	 * @param string $input_name Input name.
	 *
	 * @return mixed|bool False on failure
	 */
	function woograbexpress_calc_shipping_field_value( $input_name ) {
		$nonce_field  = 'woocommerce-shipping-calculator-nonce';
		$nonce_action = 'woocommerce-shipping-calculator';

		if ( isset( $_POST['calc_shipping'], $_POST[ $input_name ], $_POST[ $nonce_field ] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ $nonce_field ] ) ), $nonce_action ) ) {
			return sanitize_text_field( wp_unslash( $_POST[ $input_name ] ) );
		}

		return false;
	}
endif;

if ( ! function_exists( 'woograbexpress_shipping_fields' ) ) :
	/**
	 * Get shipping fields.
	 *
	 * @return array
	 */
	function woograbexpress_shipping_fields() {
		$different_address = ! empty( $_POST['ship_to_different_address'] ) && ! wc_ship_to_billing_address_only(); // phpcs:ignore WordPress
		$address_type      = $different_address ? 'shipping' : 'billing';
		$checkout_fields   = WC()->checkout->get_checkout_fields( $address_type );

		if ( ! $checkout_fields ) {
			return false;
		}

		return array(
			'type' => $address_type,
			'data' => $checkout_fields,
		);
	}
endif;
