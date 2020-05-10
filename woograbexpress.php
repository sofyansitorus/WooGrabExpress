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
 * @package           WooGrabExpress
 *
 * @wordpress-plugin
 * Plugin Name:       WooGrabExpress
 * Plugin URI:        https://github.com/sofyansitorus/WooGrabExpress
 * Description:       WooCommerce per kilometer shipping rates calculator for GrabExpress delivery service from Grab Indonesia.
 * Version:           1.4.0
 * Author:            Sofyan Sitorus
 * Author URI:        https://github.com/sofyansitorus
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woograbexpress
 * Domain Path:       /languages
 *
 * WC requires at least: 3.0.0
 * WC tested up to: 4.1.0
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

// Define plugin main constants.
define( 'WOOGRABEXPRESS_FILE', __FILE__ );
define( 'WOOGRABEXPRESS_PATH', plugin_dir_path( WOOGRABEXPRESS_FILE ) );
define( 'WOOGRABEXPRESS_URL', plugin_dir_url( WOOGRABEXPRESS_FILE ) );

// Load the helpers.
require_once WOOGRABEXPRESS_PATH . '/includes/constants.php';
require_once WOOGRABEXPRESS_PATH . '/includes/helpers.php';

// Register the class autoload.
if ( function_exists( 'woograbexpress_autoload' ) ) {
	spl_autoload_register( 'woograbexpress_autoload' );
}

/**
 * Boot the plugin
 */
if ( woograbexpress_is_plugin_active( 'woocommerce/woocommerce.php' ) && class_exists( 'WooGrabExpress' ) ) {
	// Initialize the WooGrabExpress class.
	WooGrabExpress::get_instance();
}

