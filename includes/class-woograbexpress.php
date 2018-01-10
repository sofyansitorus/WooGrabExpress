<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://github.com/sofyansitorus
 * @since      1.0.0
 *
 * @package    WooGrabExpress
 * @subpackage WooGrabExpress/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    WooGrabExpress
 * @subpackage WooGrabExpress/includes
 * @author     Sofyan Sitorus <sofyansitorus@gmail.com>
 */
class WooGrabExpress extends WC_Shipping_Method {
	/**
	 * URL of Google Maps Distance Matrix API
	 *
	 * @since    1.0.0
	 * @var string
	 */
	private $google_api_url = 'https://maps.googleapis.com/maps/api/distancematrix/json';

	/**
	 * Constructor for your shipping class
	 *
	 * @since    1.0.0
	 * @param int $instance_id ID of shipping method instance.
	 */
	public function __construct( $instance_id = 0 ) {
		// ID for your shipping method. Should be unique.
		$this->id = 'woograbexpress';

		// Title shown in admin.
		$this->method_title = 'WooGrabExpress';

		// Description shown in admin.
		$this->method_description = __( 'Shipping rates calculator for GrabExpress courier from Grab Indonesia.', 'woograbexpress' );

		$this->enabled = $this->get_option( 'enabled' );

		$this->instance_id = absint( $instance_id );

		$this->supports = array(
			'shipping-zones',
			'instance-settings',
			'instance-settings-modal',
		);

		$this->init();
	}

	/**
	 * Init settings
	 *
	 * @since    1.0.0
	 * @return void
	 */
	public function init() {
		// Load the settings API.
		$this->init_form_fields(); // This is part of the settings API. Override the method to add your own settings
		$this->init_settings(); // This is part of the settings API. Loads settings you previously init.

		// Define user set variables.
		$this->title           = $this->get_option( 'title', 'GrabExpress' );
		$this->gmaps_api_key   = $this->get_option( 'gmaps_api_key' );
		$this->origin_lat      = $this->get_option( 'origin_lat' );
		$this->origin_lng      = $this->get_option( 'origin_lng' );
		$this->gmaps_api_mode  = $this->get_option( 'gmaps_api_mode' );
		$this->gmaps_api_avoid = $this->get_option( 'gmaps_api_avoid' );
		$this->cost_per_km     = $this->get_option( 'cost_per_km' );
		$this->min_cost        = $this->get_option( 'min_cost' );
		$this->max_cost        = $this->get_option( 'max_cost' );
		$this->max_width       = $this->get_option( 'max_width' );
		$this->max_length      = $this->get_option( 'max_length' );
		$this->max_height      = $this->get_option( 'max_height' );
		$this->max_weight      = $this->get_option( 'max_weight' );
		$this->min_distance    = $this->get_option( 'min_distance' );
		$this->max_distance    = $this->get_option( 'max_distance' );
		$this->show_distance   = $this->get_option( 'show_distance' );
		$this->tax_status      = $this->get_option( 'tax_status' );

		// Save settings in admin if you have any defined.
		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );

		// Check if this shipping method is availbale for current order.
		add_filter( 'woocommerce_shipping_' . $this->id . '_is_available', array( $this, 'check_is_available' ), 10, 2 );
	}

	/**
	 * Init form fields.
	 *
	 * @since    1.0.0
	 */
	public function init_form_fields() {
		$this->instance_form_fields = array(
			'title'             => array(
				'title'       => __( 'Title', 'woograbexpress' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'woograbexpress' ),
				'default'     => $this->method_title,
				'desc_tip'    => true,
			),
			'gmaps_api_key'     => array(
				'title'       => __( 'API Key', 'woograbexpress' ),
				'type'        => 'text',
				'description' => __( '<a href="https://developers.google.com/maps/documentation/distance-matrix/get-api-key" target="_blank">Click here</a> to get a Google Maps Distance Matrix API Key.', 'woograbexpress' ),
				'default'     => '',
			),
			'gmaps_api_mode'    => array(
				'title'       => __( 'Travel Mode', 'woograbexpress' ),
				'type'        => 'select',
				'description' => __( 'Google Maps Distance Matrix API travel mode parameter.', 'woograbexpress' ),
				'desc_tip'    => true,
				'default'     => 'driving',
				'options'     => array(
					'driving'   => __( 'Driving', 'woograbexpress' ),
					'walking'   => __( 'Walking', 'woograbexpress' ),
					'bicycling' => __( 'Bicycling', 'woograbexpress' ),
				),
			),
			'gmaps_api_avoid'   => array(
				'title'       => __( 'Restrictions', 'woograbexpress' ),
				'type'        => 'multiselect',
				'description' => __( 'Google Maps Distance Matrix API restrictions parameter.', 'woograbexpress' ),
				'desc_tip'    => true,
				'default'     => 'driving',
				'options'     => array(
					'tolls'    => __( 'Avoid Tolls', 'woograbexpress' ),
					'highways' => __( 'Avoid Highways', 'woograbexpress' ),
					'ferries'  => __( 'Avoid Ferries', 'woograbexpress' ),
					'indoor'   => __( 'Avoid Indoor', 'woograbexpress' ),
				),
			),
			'origin_lat'        => array(
				'title'       => __( 'Store Location Latitude', 'woograbexpress' ),
				'type'        => 'decimal',
				'description' => __( '<a href="http://www.latlong.net/" target="_blank">Click here</a> to get your store location coordinates info.', 'woograbexpress' ),
				'default'     => '',
			),
			'origin_lng'        => array(
				'title'       => __( 'Store Location Longitude', 'woograbexpress' ),
				'type'        => 'decimal',
				'description' => __( '<a href="http://www.latlong.net/" target="_blank">Click here</a> to get your store location coordinates info.', 'woograbexpress' ),
				'default'     => '',
			),
			'tax_status'        => array(
				'title'   => __( 'Tax status', 'woograbexpress' ),
				'type'    => 'select',
				'class'   => 'wc-enhanced-select',
				'default' => 'taxable',
				'options' => array(
					'taxable' => __( 'Taxable', 'woograbexpress' ),
					'none'    => __( 'None', 'woograbexpress' ),
				),
			),
			'grabexpress_title' => array(
				'title'       => __( 'GrabExpress Service Options', 'woograbexpress' ),
				'type'        => 'title',
				'description' => __( '<a href="https://www.grab.com/id/express/" target="_blank">Click here</a> for more info about GrabExpress.', 'woograbexpress' ),
			),
			'cost_per_km'       => array(
				'title'       => __( 'Cost per Kilometer', 'woograbexpress' ),
				'type'        => 'price',
				'description' => __( 'Per kilometer rates that will be billed to customer.', 'woograbexpress' ),
				'desc_tip'    => true,
				'default'     => '2500',
			),
			'min_cost'          => array(
				'title'       => __( 'Minimum Cost', 'woograbexpress' ),
				'type'        => 'price',
				'description' => __( 'Minimum shipping cost that will be billed to customer. Leave blank to disable.', 'woograbexpress' ),
				'desc_tip'    => true,
				'default'     => '15000',
			),
			'max_cost'          => array(
				'title'       => __( 'Maximum Cost', 'woograbexpress' ),
				'type'        => 'price',
				'description' => __( 'Maximum shipping cost that will be billed to customer. Leave blank to disable.', 'woograbexpress' ),
				'desc_tip'    => true,
				'default'     => '',
			),
			'max_weight'        => array(
				'title'             => __( 'Maximum Package Weight', 'woograbexpress' ),
				'type'              => 'number',
				'description'       => __( 'Maximum package weight in kilograms that will be allowed to use this courier. Leave blank to disable.', 'woograbexpress' ),
				'desc_tip'          => true,
				'default'           => '5',
				'custom_attributes' => array( 'min' => '1' ),
			),
			'max_width'         => array(
				'title'             => __( 'Maximum Package Width', 'woograbexpress' ),
				'type'              => 'number',
				'description'       => __( 'Maximum package size width in centimeters that will be allowed to use this courier. Leave blank to disable.', 'woograbexpress' ),
				'desc_tip'          => true,
				'default'           => '25',
				'custom_attributes' => array( 'min' => '1' ),
			),
			'max_length'        => array(
				'title'             => __( 'Maximum Package Length', 'woograbexpress' ),
				'type'              => 'number',
				'description'       => __( 'Maximum package size length in centimeters that will be allowed to use this courier. Leave blank to disable.', 'woograbexpress' ),
				'desc_tip'          => true,
				'default'           => '32',
				'custom_attributes' => array( 'min' => '1' ),
			),
			'max_height'        => array(
				'title'             => __( 'Maximum Package Height', 'woograbexpress' ),
				'type'              => 'number',
				'description'       => __( 'Maximum package size height in centimeters that will be allowed to use this courier. Leave blank to disable.', 'woograbexpress' ),
				'desc_tip'          => true,
				'default'           => '12',
				'custom_attributes' => array( 'min' => '1' ),
			),
			'min_distance'      => array(
				'title'             => __( 'Minimum Distance', 'woograbexpress' ),
				'type'              => 'number',
				'description'       => __( 'Minimum distance in kilometers that will be allowed to use this courier. Leave blank to disable.', 'woograbexpress' ),
				'desc_tip'          => true,
				'default'           => '1',
				'custom_attributes' => array( 'min' => '1' ),
			),
			'max_distance'      => array(
				'title'             => __( 'Maximum Distance', 'woograbexpress' ),
				'type'              => 'number',
				'description'       => __( 'Maximum distance in kilometers that will be allowed to use this courier. Leave blank to disable.', 'woograbexpress' ),
				'desc_tip'          => true,
				'default'           => '40',
				'custom_attributes' => array( 'min' => '1' ),
			),
			'show_distance'     => array(
				'title'       => __( 'Show distance', 'woograbexpress' ),
				'label'       => __( 'Yes', 'woograbexpress' ),
				'type'        => 'checkbox',
				'description' => __( 'Show the distance info to customer during checkout.', 'woograbexpress' ),
				'desc_tip'    => true,
			),
		);
	}

	/**
	 * Validate gmaps_api_key settings field.
	 *
	 * @since    1.0.0
	 * @param  string $key Settings field key.
	 * @param  string $value Posted field value.
	 * @throws Exception If the field value is invalid.
	 * @return string
	 */
	public function validate_gmaps_api_key_field( $key, $value ) {
		try {
			if ( empty( $value ) ) {
				throw new Exception( __( 'API Key is required', 'woograbexpress' ) );
			}
			return $value;
		} catch ( Exception $e ) {
			$this->add_error( $e->getMessage() );
			return $this->gmaps_api_key;
		}
	}

	/**
	 * Validate origin_lat settings field.
	 *
	 * @since    1.0.0
	 * @param  string $key Settings field key.
	 * @param  string $value Posted field value.
	 * @throws Exception If the field value is invalid.
	 * @return string
	 */
	public function validate_origin_lat_field( $key, $value ) {
		try {
			if ( empty( $value ) ) {
				throw new Exception( __( 'Store Location Latitude is required', 'woograbexpress' ) );
			}
			return $value;
		} catch ( Exception $e ) {
			$this->add_error( $e->getMessage() );
			return $this->origin_lat;
		}
	}

	/**
	 * Validate origin_lng settings field.
	 *
	 * @since    1.0.0
	 * @param  string $key Settings field key.
	 * @param  string $value Posted field value.
	 * @throws Exception If the field value is invalid.
	 * @return string
	 */
	public function validate_origin_lng_field( $key, $value ) {
		try {
			if ( empty( $value ) ) {
				throw new Exception( __( 'Store Location Longitude is required', 'woograbexpress' ) );
			}
			return $value;
		} catch ( Exception $e ) {
			$this->add_error( $e->getMessage() );
			return $this->origin_lng;
		}
	}

	/**
	 * Check if this method available
	 *
	 * @since    1.0.0
	 * @param boolean $available Current status is available.
	 * @param array   $package Current order package data.
	 * @return bool
	 */
	public function check_is_available( $available, $package ) {
		if ( ! $available || empty( $package['contents'] ) || empty( $package['destination'] ) ) {
			return false;
		}

		if ( 'ID' !== WC()->countries->get_base_country() ) {
			return false;
		}

		return $available;
	}

	/**
	 * Calculate shipping function.
	 *
	 * @since    1.0.0
	 * @param array $package Package data array.
	 * @throws Exception If the item weight and dimensions exceeded the limit.
	 */
	public function calculate_shipping( $package = array() ) {
		$shipping_cost_total = 0;

		if ( empty( $this->cost_per_km ) ) {
			return;
		}

		$api_request = $this->api_request( $package['destination'] );
		if ( ! $api_request ) {
			return;
		}
		if ( $this->min_distance && $api_request['distance'] < $this->min_distance ) {
			return;
		}
		if ( $this->max_distance && $api_request['distance'] > $this->max_distance ) {
			return;
		}

		$drivers_count = 1;

		$item_weight_bulk = array();
		$item_width_bulk  = array();
		$item_length_bulk = array();
		$item_height_bulk = array();

		foreach ( $package['contents'] as $hash => $item ) {
			// Check if item weight is not exceeded maximum package weight allowed.
			$item_weight = wc_get_weight( $item['data']->get_weight(), 'kg' ) * $item['quantity'];
			if ( $this->max_weight && $item_weight > $this->max_weight ) {
				return;
			}

			// Check if item width is not exceeded maximum package width allowed.
			$item_width = wc_get_dimension( $item['data']->get_width(), 'cm' );
			if ( $this->max_width && $item_width > $this->max_width ) {
				return;
			}

			// Check if item length is not exceeded maximum package length allowed.
			$item_length = wc_get_dimension( $item['data']->get_length(), 'cm' );
			if ( $this->max_length && $item_length > $this->max_length ) {
				return;
			}

			// Check if item height is not exceeded maximum package height allowed.
			$item_height = wc_get_dimension( $item['data']->get_height(), 'cm' ) * $item['quantity'];
			if ( $this->max_height && $item_height > $this->max_height ) {
				return;
			}

			// Try to split the order for several shipments.
			try {
				$item_weight_bulk[] = $item_weight;
				if ( $this->max_weight && array_sum( $item_weight_bulk ) > $this->max_weight ) {
					throw new Exception( 'Exceeded maximum package weight', 1 );
				}

				$item_width_bulk[] = $item_width;
				if ( $this->max_width && max( $item_width_bulk ) > $this->max_width ) {
					throw new Exception( 'Exceeded maximum package width', 1 );
				}

				$item_length_bulk[] = $item_length;
				if ( $this->max_length && max( $item_length_bulk ) > $this->max_length ) {
					throw new Exception( 'Exceeded maximum package length', 1 );
				}

				$item_height_bulk[] = $item_height;
				if ( $this->max_height && array_sum( $item_height_bulk ) > $this->max_height ) {
					throw new Exception( 'Exceeded maximum package height', 1 );
				}
			} catch ( Exception $e ) {
				$item_weight_bulk = array();
				$item_width_bulk  = array();
				$item_length_bulk = array();
				$item_height_bulk = array();

				$drivers_count++;

				continue;
			}
		}

		$shipping_cost_total = $this->cost_per_km * $api_request['distance'];

		if ( $this->min_cost && $shipping_cost_total < $this->min_cost ) {
			$shipping_cost_total = $this->min_cost;
		}

		if ( $this->max_cost && $shipping_cost_total > $this->max_cost ) {
			$shipping_cost_total = $this->max_cost;
		}

		$shipping_cost_total *= $drivers_count;

		$drivers_count_text = sprintf( _n( '%s driver', '%s drivers', $drivers_count, 'woograbexpress' ), $drivers_count );

		switch ( $this->show_distance ) {
			case 'yes':
				$label = ( $drivers_count > 1 ) ? sprintf( '%s (%s, %s)', $this->title, $drivers_count_text, $api_request['distance_text'] ) : sprintf( '%s (%s)', $this->title, $api_request['distance_text'] );
				break;
			default:
				$label = ( $drivers_count > 1 ) ? sprintf( '%s (%s)', $this->title, $drivers_count_text, $api_request['distance_text'] ) : $this->title;
				break;
		}

		$rate = array(
			'id'        => $this->id . '_' . $drivers_count,
			'label'     => $label,
			'cost'      => $shipping_cost_total,
			'meta_data' => $api_request,
		);

		// Register the rate.
		$this->add_rate( $rate );

		/**
		 * Developers can add additional rates via action.
		 *
		 * This example shows how you can add an extra rate via custom function:
		 *
		 *      add_action( 'woocommerce_woograbexpress_shipping_add_rate', 'add_another_custom_flat_rate', 10, 2 );
		 *
		 *      function add_another_custom_flat_rate( $method, $rate ) {
		 *          $new_rate          = $rate;
		 *          $new_rate['id']    .= ':' . 'custom_rate_name'; // Append a custom ID.
		 *          $new_rate['label'] = 'Rushed Shipping'; // Rename to 'Rushed Shipping'.
		 *          $new_rate['cost']  += 2; // Add $2 to the cost.
		 *
		 *          // Add it to WC.
		 *          $method->add_rate( $new_rate );
		 *      }.
		 */
		do_action( 'woocommerce_' . $this->id . '_shipping_add_rate', $this, $rate );
	}

	/**
	 * Making HTTP request to Google Maps Distance Matrix API
	 *
	 * @since    1.0.0
	 * @param array $destination Destination info in assciative array: address, address_2, city, state, postcode, country.
	 * @return array
	 */
	private function api_request( $destination ) {

		if ( empty( $this->gmaps_api_key ) ) {
			return false;
		}

		$destination = $this->get_destination_info( $destination );
		if ( empty( $destination ) ) {
			return false;
		}

		$origins = $this->get_origin_info();
		if ( empty( $origins ) ) {
			return false;
		}

		$cache_keys = array(
			$this->gmaps_api_key,
			$destination,
			$origins,
			$this->gmaps_api_mode,
		);

		$route_avoid = $this->gmaps_api_avoid;
		if ( is_array( $route_avoid ) ) {
			$route_avoid = implode( ',', $route_avoid );
		}
		if ( $route_avoid ) {
			array_push( $cache_keys, $route_avoid );
		}

		$cache_key = implode( '_', $cache_keys );

		// Check if the data already chached and return it.
		$cached_data = wp_cache_get( $cache_key, $this->id );
		if ( false !== $cached_data ) {
			$this->show_debug( 'Google Maps Distance Matrix API cache key: ' . $cache_key );
			$this->show_debug( 'Cached Google Maps Distance Matrix API response: ' . wp_json_encode( $cached_data ) );
			return $cached_data;
		}

		$request_url = add_query_arg(
			array(
				'key'          => rawurlencode( $this->gmaps_api_key ),
				'units'        => rawurlencode( 'metric' ),
				'mode'         => rawurlencode( $this->gmaps_api_mode ),
				'avoid'        => rawurlencode( $route_avoid ),
				'destinations' => rawurlencode( $destination ),
				'origins'      => rawurlencode( $origins ),
			),
			$this->google_api_url
		);
		$this->show_debug( 'Google Maps Distance Matrix API request URL: ' . $request_url );

		$response = wp_remote_retrieve_body( wp_remote_get( esc_url_raw( $request_url ) ) );
		$this->show_debug( 'Google Maps Distance Matrix API response: ' . $response );

		$response = json_decode( $response, true );

		if ( json_last_error() !== JSON_ERROR_NONE || empty( $response['rows'] ) ) {
			return false;
		}

		if ( empty( $response['destination_addresses'] ) || empty( $response['origin_addresses'] ) ) {
			return false;
		}

		$distance = 0;

		foreach ( $response['rows'] as $rows ) {
			foreach ( $rows['elements'] as $element ) {
				if ( 'OK' === $element['status'] ) {
					$element_distance = ceil( str_replace( ' km', '', $element['distance']['text'] ) );
					if ( $element_distance > $distance ) {
						$distance      = $element_distance;
						$distance_text = $distance . ' km';
					}
				}
			}
		}

		if ( $distance ) {
			$data = array(
				'distance'      => $distance,
				'distance_text' => $distance_text,
				'response'      => $response,
			);

			wp_cache_set( $cache_key, $data, $this->id ); // Store the data to WP Object Cache for later use.

			return $data;
		}

		return false;
	}

	/**
	 * Get shipping origin info
	 *
	 * @since    1.0.0
	 * @return string
	 */
	private function get_origin_info() {
		$origin_info = array();

		if ( ! empty( $this->origin_lat ) && ! empty( $this->origin_lng ) ) {
			array_push( $origin_info, $this->origin_lat, $this->origin_lng );
		}

		/**
		 * Developers can modify the origin info via filter hooks.
		 *
		 * @since 1.0.1
		 *
		 * This example shows how you can modify the shipping origin info via custom function:
		 *
		 *      add_action( 'woocommerce_woograbexpress_shipping_origin_info', 'modify_shipping_origin_info', 10, 2 );
		 *
		 *      function modify_shipping_origin_info( $origin_info, $method ) {
		 *          return '1600 Amphitheatre Parkway,Mountain View,CA,94043';
		 *      }
		 */
		return apply_filters( 'woocommerce_' . $this->id . '_shipping_origin_info', implode( ',', $origin_info ), $this );
	}

	/**
	 * Get shipping destination info
	 *
	 * @since    1.0.0
	 * @param array $data Shipping destination data in associative array format: address, city, state, postcode, country.
	 * @return string
	 */
	private function get_destination_info( $data ) {
		$destination_info = array();

		$keys = array( 'address', 'address_2', 'city', 'state', 'postcode', 'country' );

		// Remove destination field keys for shipping calculator request.
		if ( ! empty( $_POST['calc_shipping'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'woocommerce-cart' ) ) {
			$keys_remove = array( 'address', 'address_2' );
			if ( ! apply_filters( 'woocommerce_shipping_calculator_enable_city', false ) ) {
				array_push( $keys_remove, 'city' );
			}
			if ( ! apply_filters( 'woocommerce_shipping_calculator_enable_postcode', false ) ) {
				array_push( $keys_remove, 'postcode' );
			}
			$keys = array_diff( $keys, $keys_remove );
		}

		$country_code = false;

		foreach ( $keys as $key ) {
			if ( ! isset( $data[ $key ] ) || empty( $data[ $key ] ) ) {
				continue;
			}
			switch ( $key ) {
				case 'country':
					if ( empty( $country_code ) ) {
						$country_code = $data[ $key ];
					}
					$full_country       = isset( WC()->countries->countries[ $country_code ] ) ? WC()->countries->countries[ $country_code ] : $country_code;
					$destination_info[] = trim( $full_country );
					break;
				case 'state':
					if ( empty( $country_code ) ) {
						$country_code = $data['country'];
					}
					$full_state         = isset( WC()->countries->states[ $country_code ][ $data[ $key ] ] ) ? WC()->countries->states[ $country_code ][ $data[ $key ] ] : $data[ $key ];
					$destination_info[] = trim( $full_state );
					break;
				default:
					$destination_info[] = trim( $data[ $key ] );
					break;
			}
		}

		/**
		 * Developers can modify the destination info via filter hooks.
		 *
		 * @since 1.0.1
		 *
		 * This example shows how you can modify the shipping destination info via custom function:
		 *
		 *      add_action( 'woocommerce_woograbexpress_shipping_destination_info', 'modify_shipping_destination_info', 10, 2 );
		 *
		 *      function modify_shipping_destination_info( $destination_info, $method ) {
		 *          return '1600 Amphitheatre Parkway,Mountain View,CA,94043';
		 *      }
		 */
		return apply_filters( 'woocommerce_' . $this->id . '_shipping_destination_info', implode( ',', $destination_info ), $this );
	}

	/**
	 * Show debug info
	 *
	 * @since    1.0.0
	 * @param string $message The text to display in the notice.
	 * @return void
	 */
	private function show_debug( $message ) {
		$debug_mode = 'yes' === get_option( 'woocommerce_shipping_debug_mode', 'no' );

		if ( $debug_mode && ! defined( 'WOOCOMMERCE_CHECKOUT' ) && ! defined( 'WC_DOING_AJAX' ) && ! wc_has_notice( $message ) ) {
			wc_add_notice( $message );
		}
	}
}
