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
 * Description:       WooCommerce per kilometer shipping rates calculator for GrabExpress Grab Indonesia courier.
 * Version:           1.2.3
 * Author:            Sofyan Sitorus
 * Author URI:        https://github.com/sofyansitorus
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woograbexpress
 * Domain Path:       /languages
 *
 * WC requires at least: 3.0.0
 * WC tested up to: 3.3.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Check if WooCommerce is active
 */
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
	return;
}// End if().

// Defines plugin named constants.
define( 'WOOGRABEXPRESS_PATH', plugin_dir_path( __FILE__ ) );
define( 'WOOGRABEXPRESS_URL', plugin_dir_url( __FILE__ ) );
define( 'WOOGRABEXPRESS_VERSION', '1.2.3' );
define( 'WOOGRABEXPRESS_METHOD_ID', 'woograbexpress' );
define( 'WOOGRABEXPRESS_METHOD_TITLE', 'WooGrabExpress' );

/**
 * Load plugin textdomain.
 *
 * @since 1.0.0
 */
function woograbexpress_load_textdomain() {
	load_plugin_textdomain( 'woograbexpress', false, basename( dirname( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'woograbexpress_load_textdomain' );

/**
 * Load the main class
 *
 * @since    1.0.0
 */
function woograbexpress_shipping_init() {
	include plugin_dir_path( __FILE__ ) . 'includes/class-woograbexpress.php';
}
add_action( 'woocommerce_shipping_init', 'woograbexpress_shipping_init' );

/**
 * Add plugin action links.
 *
 * Add a link to the settings page on the plugins.php page.
 *
 * @since 1.1.1
 *
 * @param  array $links List of existing plugin action links.
 * @return array         List of modified plugin action links.
 */
function woograbexpress_plugin_action_links( $links ) {
	$zone_id = 0;
	$zones   = WC_Shipping_Zones::get_zones();
	foreach ( $zones as $zone ) {
		if ( empty( $zone['shipping_methods'] ) || empty( $zone['zone_id'] ) ) {
			continue;
		}
		foreach ( $zone['shipping_methods'] as $zone_shipping_method ) {
			if ( $zone_shipping_method instanceof WooGrabExpress ) {
				$zone_id = $zone['zone_id'];
				break;
			}
		}
	}

	$links = array_merge(
		array(
			'<a href="' . esc_url( wp_nonce_url( admin_url( 'admin.php?page=wc-settings&tab=shipping&zone_id=' . $zone_id ), 'woograbexpress_settings', 'woograbexpress_nonce' ) ) . '">' . __( 'Settings', 'woograbexpress' ) . '</a>',
		),
		$links
	);

	return $links;
}
add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'woograbexpress_plugin_action_links' );

/**
 * Enqueue admin scripts.
 *
 * @since 1.1.1
 * @param string $hook Passed screen ID in admin area.
 */
function woograbexpress_enqueue_scripts( $hook = null ) {
	if ( 'woocommerce_page_wc-settings' === $hook ) {

		// Enqueue admin scripts.
		$woograbexpress_admin_css = ( defined( 'WOOGRABEXPRESS_DEV' ) && WOOGRABEXPRESS_DEV ) ? add_query_arg( array( 't' => time() ), WOOGRABEXPRESS_URL . 'assets/css/woograbexpress-admin.css' ) : WOOGRABEXPRESS_URL . 'assets/css/woograbexpress-admin.min.css';
		wp_enqueue_style(
			'woograbexpress-admin', // Give the script a unique ID.
			$woograbexpress_admin_css, // Define the path to the JS file.
			array(), // Define dependencies.
			WOOGRABEXPRESS_VERSION, // Define a version (optional).
			false // Specify whether to put in footer (leave this false).
		);

		// Enqueue admin scripts.
		$woograbexpress_admin_js = ( defined( 'WOOGRABEXPRESS_DEV' ) && WOOGRABEXPRESS_DEV ) ? add_query_arg( array( 't' => time() ), WOOGRABEXPRESS_URL . 'assets/js/woograbexpress-admin.js' ) : WOOGRABEXPRESS_URL . 'assets/js/woograbexpress-admin.min.js';
		wp_enqueue_script(
			'woograbexpress-admin', // Give the script a unique ID.
			$woograbexpress_admin_js, // Define the path to the JS file.
			array( 'jquery', 'wp-util' ), // Define dependencies.
			WOOGRABEXPRESS_VERSION, // Define a version (optional).
			true // Specify whether to put in footer (leave this true).
		);
		wp_localize_script(
			'woograbexpress-admin',
			'woograbexpress_params',
			array(
				'show_settings' => ( isset( $_GET['woograbexpress_nonce'] ) && wp_verify_nonce( $_GET['woograbexpress_nonce'], 'woograbexpress_settings' ) && is_admin() ),
				'method_id'     => WOOGRABEXPRESS_METHOD_ID,
				'method_title'  => WOOGRABEXPRESS_METHOD_TITLE,
				'txt'           => array(
					'drag_marker' => __( 'Drag this marker or search your address at the input above.', 'woograbexpress' ),
				),
				'marker'        => WOOGRABEXPRESS_URL . 'assets/img/marker.png',
			)
		);

	}
}
add_action( 'admin_enqueue_scripts', 'woograbexpress_enqueue_scripts', 999 );

/**
 * Register shipping method
 *
 * @since    1.0.0
 * @param array $methods Existing shipping methods.
 */
function woograbexpress_shipping_methods( $methods ) {
	$methods['woograbexpress'] = 'WooGrabExpress';
	return $methods;
}
add_filter( 'woocommerce_shipping_methods', 'woograbexpress_shipping_methods' );

// Show city field on the cart shipping calculator.
add_filter( 'woocommerce_shipping_calculator_enable_city', '__return_true' );
