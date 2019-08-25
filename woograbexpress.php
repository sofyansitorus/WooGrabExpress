<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/sofyansitorus
 * @since             1.0.0
 * @package           WooGrabExpress
 *
 * @wordpress-plugin
 * Plugin Name:       WooGrabExpress
 * Plugin URI:        https://github.com/sofyansitorus/WooGrabExpress
 * Description:       WooCommerce per kilometer shipping rates calculator for GrabExpress delivery service from Grab Indonesia.
 * Version:           1.2.4
 * Author:            Sofyan Sitorus
 * Author URI:        https://github.com/sofyansitorus
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woograbexpress
 * Domain Path:       /languages
 *
 * WC requires at least: 3.0.0
 * WC tested up to: 3.7.0
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

// Defines plugin named constants.
if ( ! defined( 'WOOGRABEXPRESS_FILE' ) ) {
	define( 'WOOGRABEXPRESS_FILE', __FILE__ );
}
if ( ! defined( 'WOOGRABEXPRESS_PATH' ) ) {
	define( 'WOOGRABEXPRESS_PATH', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'WOOGRABEXPRESS_URL' ) ) {
	define( 'WOOGRABEXPRESS_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'WOOGRABEXPRESS_DEFAULT_LAT' ) ) {
	define( 'WOOGRABEXPRESS_DEFAULT_LAT', '-6.178784361374902' );
}
if ( ! defined( 'WOOGRABEXPRESS_DEFAULT_LNG' ) ) {
	define( 'WOOGRABEXPRESS_DEFAULT_LNG', '106.82303292695315' );
}
if ( ! defined( 'WOOGRABEXPRESS_TEST_LAT' ) ) {
	define( 'WOOGRABEXPRESS_TEST_LAT', '-6.181472315327319' );
}
if ( ! defined( 'WOOGRABEXPRESS_TEST_LNG' ) ) {
	define( 'WOOGRABEXPRESS_TEST_LNG', '106.8170462364319' );
}

if ( ! function_exists( 'get_plugin_data' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

$woograbexpress_plugin_data = get_plugin_data( WOOGRABEXPRESS_FILE, false, false );

if ( ! defined( 'WOOGRABEXPRESS_VERSION' ) ) {
	$woograbexpress_version = isset( $woograbexpress_plugin_data['Version'] ) ? $woograbexpress_plugin_data['Version'] : '1.0.0';
	define( 'WOOGRABEXPRESS_VERSION', $woograbexpress_version );
}

if ( ! defined( 'WOOGRABEXPRESS_METHOD_ID' ) ) {
	$woograbexpress_method_id = isset( $woograbexpress_plugin_data['TextDomain'] ) ? $woograbexpress_plugin_data['TextDomain'] : 'woograbexpress';
	define( 'WOOGRABEXPRESS_METHOD_ID', $woograbexpress_method_id );
}

if ( ! defined( 'WOOGRABEXPRESS_METHOD_TITLE' ) ) {
	$woograbexpress_method_title = isset( $woograbexpress_plugin_data['Name'] ) ? $woograbexpress_plugin_data['Name'] : 'WooGrabExpress';
	define( 'WOOGRABEXPRESS_METHOD_TITLE', $woograbexpress_method_title );
}

/**
 * Include required core files.
 */
require_once WOOGRABEXPRESS_PATH . '/includes/helpers.php';
require_once WOOGRABEXPRESS_PATH . '/includes/class-woograbexpress-api.php';
require_once WOOGRABEXPRESS_PATH . '/includes/class-woograbexpress-services.php';

/**
 * Check if WooCommerce plugin is active
 */
if ( ! woograbexpress_is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
	return;
}

// Show fields in the shipping calculator form.
add_filter( 'woocommerce_shipping_calculator_enable_postcode', '__return_true' );
add_filter( 'woocommerce_shipping_calculator_enable_state', '__return_true' );
add_filter( 'woocommerce_shipping_calculator_enable_city', '__return_true' );
add_filter( 'woocommerce_shipping_calculator_enable_address_1', '__return_true' );
add_filter( 'woocommerce_shipping_calculator_enable_address_2', '__return_true' );

/**
 * Load plugin textdomain.
 *
 * @since 1.0.0
 *
 * @return void
 */
function woograbexpress_load_textdomain() {
	load_plugin_textdomain( 'woograbexpress', false, basename( WOOGRABEXPRESS_PATH ) . '/languages' );
}
add_action( 'plugins_loaded', 'woograbexpress_load_textdomain' );

/**
 * Add plugin action links.
 *
 * Add a link to the settings page on the plugins.php page.
 *
 * @since 1.1.1
 *
 * @param array $links List of existing plugin action links.
 *
 * @return array List of modified plugin action links.
 */
function woograbexpress_plugin_action_links( $links ) {
	$links = array_merge(
		array(
			'<a href="' . esc_url(
				add_query_arg(
					array(
						'page'                    => 'wc-settings',
						'tab'                     => 'shipping',
						'zone_id'                 => 0,
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
add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'woograbexpress_plugin_action_links' );

/**
 * Load the main class
 *
 * @since 1.0.0
 *
 * @return void
 */
function woograbexpress_shipping_init() {
	include plugin_dir_path( __FILE__ ) . 'includes/class-woograbexpress.php';
}
add_action( 'woocommerce_shipping_init', 'woograbexpress_shipping_init' );

/**
 * Register shipping method
 *
 * @since 1.0.0
 *
 * @param array $methods Existing shipping methods.
 *
 * @return array
 */
function woograbexpress_shipping_methods( $methods ) {
	return array_merge(
		$methods,
		array(
			'woograbexpress' => 'WooGrabExpress',
		)
	);
}
add_filter( 'woocommerce_shipping_methods', 'woograbexpress_shipping_methods' );

/**
 * Enqueue both scripts and styles in the backend area.
 *
 * @since 1.3
 *
 * @param string $hook Current admin page hook.
 *
 * @return void
 */
function woograbexpress_enqueue_scripts_backend( $hook ) {
	if ( false !== strpos( $hook, 'wc-settings' ) ) {
		$is_debug = defined( 'WOOGRABEXPRESS_DEV' ) && WOOGRABEXPRESS_DEV;

		// Define the styles URL.
		$css_url = WOOGRABEXPRESS_URL . 'assets/css/woograbexpress-backend.min.css';
		if ( $is_debug ) {
			$css_url = add_query_arg( array( 't' => time() ), str_replace( '.min', '', $css_url ) );
		}

		// Enqueue admin styles.
		wp_enqueue_style(
			'woograbexpress-backend', // Give the script a unique ID.
			$css_url, // Define the path to the JS file.
			array(), // Define dependencies.
			WOOGRABEXPRESS_VERSION, // Define a version (optional).
			false // Specify whether to put in footer (leave this false).
		);

		// Define the scripts URL.
		$js_url = WOOGRABEXPRESS_URL . 'assets/js/woograbexpress-backend.min.js';
		if ( $is_debug ) {
			$js_url = add_query_arg( array( 't' => time() ), str_replace( '.min', '', $js_url ) );
		}

		// Enqueue admin scripts.
		wp_enqueue_script(
			'woograbexpress-backend', // Give the script a unique ID.
			$js_url, // Define the path to the JS file.
			array( 'jquery' ), // Define dependencies.
			WOOGRABEXPRESS_VERSION, // Define a version (optional).
			true // Specify whether to put in footer (leave this true).
		);

		// Localize the script data.
		wp_localize_script(
			'woograbexpress-backend',
			'woograbexpress_backend',
			array(
				'showSettings'           => isset( $_GET['woograbexpress_settings'] ) && is_admin(), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				'methodId'               => WOOGRABEXPRESS_METHOD_ID,
				'methodTitle'            => WOOGRABEXPRESS_METHOD_TITLE,
				'marker'                 => WOOGRABEXPRESS_URL . 'assets/img/marker.png',
				'defaultLat'             => WOOGRABEXPRESS_DEFAULT_LAT,
				'defaultLng'             => WOOGRABEXPRESS_DEFAULT_LNG,
				'testLat'                => WOOGRABEXPRESS_TEST_LAT,
				'testLng'                => WOOGRABEXPRESS_TEST_LNG,
				'language'               => get_locale(),
				'isDebug'                => $is_debug,
				'i18n'                   => woograbexpress_i18n(),
				'ajax_url'               => admin_url( 'admin-ajax.php' ),
				'validate_api_key_nonce' => wp_create_nonce( 'woograbexpress_validate_api_key_server' ),
			)
		);
	}
}
add_action( 'admin_enqueue_scripts', 'woograbexpress_enqueue_scripts_backend' );

/**
 * Enqueue scripts in the frontend area.
 *
 * @since 1.3
 *
 * @return void
 */
function woograbexpress_enqueue_scripts_frontend() {
	// Bail early if there is no instances enabled.
	if ( ! woograbexpress_instances() ) {
		return;
	}

	// Define scripts URL.
	$js_url = WOOGRABEXPRESS_URL . 'assets/js/woograbexpress-frontend.min.js';
	if ( defined( 'WOOGRABEXPRESS_DEV' ) && WOOGRABEXPRESS_DEV ) {
		$js_url = add_query_arg( array( 't' => time() ), str_replace( '.min', '', $js_url ) );
	}

	// Enqueue scripts.
	wp_enqueue_script(
		'woograbexpress-frontend', // Give the script a unique ID.
		$js_url, // Define the path to the JS file.
		array( 'jquery', 'wp-util' ), // Define dependencies.
		WOOGRABEXPRESS_VERSION, // Define a version (optional).
		true // Specify whether to put in footer (leave this true).
	);

	$fields = array(
		'postcode',
		'state',
		'city',
		'address_1',
		'address_2',
	);

	// Localize the script data.
	$woograbexpress_frontend = array();

	foreach ( $fields as $field ) {
		/**
		 * Filters the shipping calculator fields
		 *
		 * @since 1.3
		 *
		 * @param bool $enabled Is field enabled status.
		 */
		$enabled = apply_filters( 'woocommerce_shipping_calculator_enable_' . $field, true ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

		$woograbexpress_frontend[ 'shipping_calculator_' . $field ] = $enabled;
	}

	wp_localize_script( 'woograbexpress-frontend', 'woograbexpress_frontend', $woograbexpress_frontend );
}
add_action( 'wp_enqueue_scripts', 'woograbexpress_enqueue_scripts_frontend' );

/**
 * Print hidden element for the custom address 1 field and address 2 field value
 * in shipping calculator form.
 *
 * @since 1.3
 *
 * @return void
 */
function woograbexpress_after_shipping_calculator() {
	// Bail early if there is no instances enabled.
	if ( ! woograbexpress_instances() ) {
		return;
	}

	// Address 1 hidden field.
	if ( apply_filters( 'woocommerce_shipping_calculator_enable_address_1', true ) ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		$address_1 = WC()->cart->get_customer()->get_shipping_address();
		?>
		<input type="hidden" id="woograbexpress-calc-shipping-field-value-address_1" value="<?php echo esc_attr( $address_1 ); ?>" />
		<?php
	}

	// Address 2 hidden field.
	if ( apply_filters( 'woocommerce_shipping_calculator_enable_address_2', true ) ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		$address_2 = WC()->cart->get_customer()->get_shipping_address_2();
		?>
		<input type="hidden" id="woograbexpress-calc-shipping-field-value-address_2" value="<?php echo esc_attr( $address_2 ); ?>" />
		<?php
	}
}
add_action( 'woocommerce_after_shipping_calculator', 'woograbexpress_after_shipping_calculator' );

/**
 * AJAX handler for Server Side API Key validation.
 *
 * @since 1.3
 *
 * @return void
 */
function woograbexpress_validate_api_key_server() {
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
add_action( 'wp_ajax_woograbexpress_validate_api_key_server', 'woograbexpress_validate_api_key_server' );
