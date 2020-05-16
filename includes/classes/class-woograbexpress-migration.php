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
abstract class WooGrabExpress_Migration {

	/**
	 * WC Shipping instance
	 *
	 * @var WC_Shipping_Method
	 */
	protected $wc_shipping;

	/**
	 * Class constructor
	 *
	 * @param WC_Shipping_Method $wc_shipping Shipping instance.
	 */
	public function __construct( $wc_shipping = false ) {
		$this->set_instance( $wc_shipping );
	}

	/**
	 * Set WC Shipping instance
	 *
	 * @param WC_Shipping_Method $wc_shipping Shipping instance.
	 *
	 * @return void
	 */
	public function set_instance( $wc_shipping ) {
		if ( ! $wc_shipping instanceof WC_Shipping_Method ) {
			return;
		}

		$this->wc_shipping = $wc_shipping;
	}

	/**
	 * Get migration options data to update
	 *
	 * @return array
	 */
	public function get_update_options() {
		if ( ! $this->wc_shipping instanceof WC_Shipping_Method ) {
			return array();
		}

		$options_pair = $this->get_options_pair();

		if ( ! $options_pair ) {
			return array();
		}

		$update_options = array();

		foreach ( $options_pair as $option_new => $option_old ) {
			if ( $option_old ) {
				$option_old_value = $this->get_old_option( $option_old, $option_new );

				if ( is_wp_error( $option_old_value ) ) {
					continue;
				}
			}

			$option_new_value = $this->get_new_option( $option_new, $option_old );

			if ( is_wp_error( $option_new_value ) ) {
				continue;
			}

			$update_options[ $option_new ] = $option_old_value;
		}

		return $update_options;
	}

	/**
	 * Get migration options keys data to delete
	 *
	 * @return array
	 */
	public function get_delete_options() {
		if ( ! $this->wc_shipping instanceof WC_Shipping_Method ) {
			return array();
		}

		$options_pair = $this->get_options_pair();

		if ( ! $options_pair ) {
			return array();
		}

		return array_values( $options_pair );
	}

	/**
	 * Get old option value.
	 *
	 * @param string $key Old option key.
	 * @param string $new_key New option key pair.
	 *
	 * @return mixed
	 */
	protected function get_old_option( $key, $new_key ) {
		if ( is_callable( array( $this, 'get_old_option__' . $key ) ) ) {
			return call_user_func( array( $this, 'get_old_option__' . $key ), $new_key );
		}

		if ( isset( $this->wc_shipping->instance_settings[ $key ] ) ) {
			return $this->wc_shipping->instance_settings[ $key ];
		}

		return new WP_Error();
	}

	/**
	 * Get new option value.
	 *
	 * @param string $key New option key.
	 * @param string $old_key Old option key.
	 *
	 * @return mixed
	 */
	protected function get_new_option( $key, $old_key ) {
		if ( is_callable( array( $this, 'get_new_option__' . $key ) ) ) {
			return call_user_func( array( $this, 'get_new_option__' . $key ), $old_key );
		}

		if ( isset( $this->wc_shipping->instance_settings[ $key ] ) ) {
			return $this->wc_shipping->instance_settings[ $key ];
		}

		return new WP_Error();
	}

	/**
	 * Get options pair data
	 *
	 * @return array
	 */
	protected function get_options_pair() {
		return array();
	}

	/**
	 * Plugin migration data version
	 *
	 * @return string
	 */
	abstract public static function get_version();
}
