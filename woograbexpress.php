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
 * Description:       WooCommerce per kilometer shipping rates calculator for GrabExpress courier from Grab Indonesia.
 * Version:           1.0.1
 * Author:            Sofyan Sitorus
 * Author URI:        https://github.com/sofyansitorus
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woograbexpress
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

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
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
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

}// End if().
