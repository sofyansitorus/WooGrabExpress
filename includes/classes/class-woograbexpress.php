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
class WooGrabExpress {

	/**
	 * Hold an instance of the class
	 *
	 * @var WooGrabExpress
	 */
	private static $instance = null;

	/**
	 * The object is created from within the class itself
	 * only if the class has no instance.
	 *
	 * @return WooGrabExpress
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new WooGrabExpress();
		}

		return self::$instance;
	}

	/**
	 * Class Constructor
	 */
	private function __construct() {
		// Set the activation hook.
		register_activation_hook( WOOGOSEND_FILE, array( $this, 'install' ) );

		// Hook to load plugin textdomain.
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

		// Hook to add plugin action links.
		add_action( 'plugin_action_links_' . plugin_basename( WOOGOSEND_FILE ), array( $this, 'plugin_action_links' ) );

		// Hook to register the shipping method.
		add_filter( 'woocommerce_shipping_methods', array( $this, 'register_shipping_method' ) );

		// Hook to AJAX actions.
		add_action( 'wp_ajax_woograbexpress_validate_api_key_server', array( $this, 'validate_api_key_server' ) );

		// Hook to modify after shipping calculator form.
		add_action( 'woocommerce_after_shipping_calculator', array( $this, 'after_shipping_calculator' ) );

		// Hook to woocommerce_cart_shipping_packages to inject filed address_2.
		add_filter( 'woocommerce_cart_shipping_packages', array( $this, 'inject_cart_shipping_packages' ) );

		// Hook to enqueue scripts & styles assets.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_backend_assets' ), 999 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ), 999 );
	}

	/**
	 * Set plugin data version
	 *
	 * @return void
	 */
	public function install() {
		update_option( 'woograbexpress_data_version', WOOGOSEND_VERSION, 'yes' );
	}

	/**
	 * Load plugin textdomain.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'woograbexpress', false, basename( WOOGOSEND_PATH ) . '/languages' );
	}

	/**
	 * Add plugin action links.
	 *
	 * Add a link to the settings page on the plugins.php page.
	 *
	 * @param  array $links List of existing plugin action links.
	 * @return array         List of modified plugin action links.
	 */
	public function plugin_action_links( $links ) {
		$zone_id = 0;

		if ( ! class_exists( 'WC_Shipping_Zones' ) ) {
			return $links;
		}

		foreach ( WC_Shipping_Zones::get_zones() as $zone ) {
			if ( empty( $zone['shipping_methods'] ) || empty( $zone['zone_id'] ) ) {
				continue;
			}

			foreach ( $zone['shipping_methods'] as $zone_shipping_method ) {
				if ( $zone_shipping_method instanceof WooGrabExpress_Shipping_Method ) {
					$zone_id = $zone['zone_id'];
					break;
				}
			}

			if ( $zone_id ) {
				break;
			}
		}

		$links = array_merge(
			array(
				'<a href="' . esc_url(
					add_query_arg(
						array(
							'page'               => 'wc-settings',
							'tab'                => 'shipping',
							'zone_id'            => $zone_id,
							'woograbexpress_settings' => true,
						),
						admin_url( 'admin.php' )
					)
				) . '">' . __( 'Settings', 'woograbexpress' ) . '</a>',
			),
			$links
		);

		return $links;
	}

	/**
	 * Enqueue backend scripts.
	 *
	 * @param string $hook Passed screen ID in admin area.
	 */
	public function enqueue_backend_assets( $hook = null ) {
		if ( false === strpos( $hook, 'wc-settings' ) ) {
			return;
		}

		$is_dev_env = woograbexpress_is_dev_env();

		// Define the styles URL.
		$css_url = WOOGOSEND_URL . 'assets/css/woograbexpress-backend.min.css';
		if ( $is_dev_env ) {
			$css_url = add_query_arg( array( 't' => time() ), str_replace( '.min', '', $css_url ) );
		}

		// Enqueue admin styles.
		wp_enqueue_style(
			'woograbexpress-backend', // Give the script a unique ID.
			$css_url, // Define the path to the JS file.
			array(), // Define dependencies.
			WOOGOSEND_VERSION, // Define a version (optional).
			false // Specify whether to put in footer (leave this false).
		);

		// Define the scripts URL.
		$js_url = WOOGOSEND_URL . 'assets/js/woograbexpress-backend.min.js';
		if ( $is_dev_env ) {
			$js_url = add_query_arg( array( 't' => time() ), str_replace( '.min', '', $js_url ) );
		}

		// Enqueue admin scripts.
		wp_enqueue_script(
			'woograbexpress-backend', // Give the script a unique ID.
			$js_url, // Define the path to the JS file.
			array( 'jquery' ), // Define dependencies.
			WOOGOSEND_VERSION, // Define a version (optional).
			true // Specify whether to put in footer (leave this true).
		);

		// Localize the script data.
		wp_localize_script(
			'woograbexpress-backend',
			'woograbexpress_backend',
			array(
				'showSettings'           => isset( $_GET['woograbexpress_settings'] ) && is_admin(), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				'methodId'               => WOOGOSEND_METHOD_ID,
				'methodTitle'            => WOOGOSEND_METHOD_TITLE,
				'marker'                 => WOOGOSEND_URL . 'assets/img/marker.png',
				'defaultLat'             => WOOGOSEND_DEFAULT_LAT,
				'defaultLng'             => WOOGOSEND_DEFAULT_LNG,
				'testLat'                => WOOGOSEND_TEST_LAT,
				'testLng'                => WOOGOSEND_TEST_LNG,
				'language'               => get_locale(),
				'isDevEnv'               => $is_dev_env,
				'i18n'                   => woograbexpress_i18n(),
				'ajax_url'               => admin_url( 'admin-ajax.php' ),
				'validate_api_key_nonce' => wp_create_nonce( 'woograbexpress_validate_api_key_server' ),
			)
		);
	}

	/**
	 * Enqueue frontend scripts.
	 */
	public function enqueue_frontend_assets() {
		// Bail early if there is no instances enabled.
		if ( ! woograbexpress_instances() ) {
			return;
		}

		$is_dev_env = woograbexpress_is_dev_env();

		// Define scripts URL.
		$js_url = WOOGOSEND_URL . 'assets/js/woograbexpress-frontend.min.js';
		if ( $is_dev_env ) {
			$js_url = add_query_arg( array( 't' => time() ), str_replace( '.min', '', $js_url ) );
		}

		// Enqueue scripts.
		wp_enqueue_script(
			'woograbexpress-frontend', // Give the script a unique ID.
			$js_url, // Define the path to the JS file.
			array( 'jquery', 'wp-util' ), // Define dependencies.
			WOOGOSEND_VERSION, // Define a version (optional).
			true // Specify whether to put in footer (leave this true).
		);
	}

	/**
	 * Register shipping method to WooCommerce.
	 *
	 * @param array $methods registered shipping methods.
	 */
	public function register_shipping_method( $methods ) {
		if ( class_exists( 'WooGrabExpress_Shipping_Method' ) ) {
			$methods[ WOOGOSEND_METHOD_ID ] = 'WooGrabExpress_Shipping_Method';
		}

		return $methods;
	}

	/**
	 * Print hidden element for the custom address 1 field and address 2 field value
	 * in shipping calculator form.
	 *
	 * @return void
	 */
	public function after_shipping_calculator() {
		// Bail early if there is no instances enabled.
		if ( ! woograbexpress_instances() ) {
			return;
		}

		$shipping_fields = woograbexpress_shipping_fields();
		if ( ! $shipping_fields ) {
			return;
		}

		$type = isset( $shipping_fields['type'] ) ? $shipping_fields['type'] : false;
		if ( ! $type ) {
			return;
		}

		$data = isset( $shipping_fields['data'] ) ? $shipping_fields['data'] : false;
		if ( ! $data ) {
			return;
		}

		$fields = array(
			'address_1' => 'get_shipping_address',
			'address_2' => 'get_shipping_address_2',
		);

		foreach ( $fields as $key => $callback ) {
			$is_enabled = apply_filters( 'woocommerce_shipping_calculator_enable_' . $key, true ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

			if ( ! $is_enabled ) {
				continue;
			}

			$field_key = $type . '_' . $key;

			$field = isset( $data[ $field_key ] ) ? $data[ $field_key ] : false;

			if ( $field ) {
				$fields[ $key ] = array(
					'value' => call_user_func( array( WC()->cart->get_customer(), $callback ) ),
					'data'  => $field,
				);
			} else {
				$fields[ $key ] = false;
			}
		}

		if ( ! array_filter( $fields ) ) {
			return;
		}

		foreach ( $fields as $key => $field ) {
			if ( ! $field ) {
				continue;
			}

			?>
			<input type="hidden" id="woograbexpress-calc-shipping-field-value-<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $field['value'] ); ?>" data-field="<?php echo esc_attr( wp_json_encode( $field['data'] ) ); ?>" />
			<?php
		}

		?>
		<script type="text/template" id="tmpl-woograbexpress-calc-shipping-custom-field">
			<p class="form-row form-row-wide" id="calc_shipping_{{ data.field }}_field">
				<input type="text" class="input-text" value="{{ data.value }}" placeholder="{{ data.placeholder }}" data-placeholder="{{ data.placeholder }}" name="calc_shipping_{{ data.field }}" id="calc_shipping_{{ data.field }}" />
			</p>
		</script>
		<?php
	}

	/**
	 * AJAX handler for Server Side API Key validation.
	 *
	 * @return void
	 */
	public function validate_api_key_server() {
		$key = isset( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'woograbexpress_validate_api_key_server' ) ) {
			wp_send_json_error( 'Sorry, your nonce did not verify.', 400 );
		}

		if ( ! $key ) {
			$key = 'InvalidKey';
		}

		$api = new WooGrabExpress_API();

		$distance = $api->calculate_distance(
			array(
				'key' => $key,
			),
			true
		);

		if ( is_wp_error( $distance ) ) {
			wp_send_json_error( $distance->get_error_message(), 400 );
		}

		wp_send_json_success( $distance );
	}

	/**
	 * Inject cart cart packages to calculate shipping for address fields.
	 *
	 * @param array $packages Current cart contents packages.
	 * @return array
	 */
	public function inject_cart_shipping_packages( $packages ) {
		if ( ! woograbexpress_is_calc_shipping() ) {
			return $packages;
		}

		$address_fields = array(
			'address_1' => false,
			'address_2' => false,
		);

		foreach ( array_keys( $address_fields ) as $field_key ) {
			$address_fields[ $field_key ] = woograbexpress_calc_shipping_field_value( 'calc_shipping_' . $field_key );
		}

		foreach ( array_keys( $packages ) as $package_key ) {
			foreach ( $address_fields as $field_key => $field_value ) {
				if ( false === $field_value ) {
					continue;
				}

				// Set customer billing address.
				call_user_func( array( WC()->customer, 'set_billing_' . $field_key ), $field_value );

				// Set customer shipping address.
				call_user_func( array( WC()->customer, 'set_shipping_' . $field_key ), $field_value );

				// Set package destination address.
				$packages[ $package_key ]['destination'][ $field_key ] = $field_value;
			}
		}

		return $packages;
	}
}
