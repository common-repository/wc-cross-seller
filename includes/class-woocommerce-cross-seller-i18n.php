<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://cleverplugins.com
 * @since      1.0.0
 *
 * @package    Woocommerce_Cross_Seller
 * @subpackage Woocommerce_Cross_Seller/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Woocommerce_Cross_Seller
 * @subpackage Woocommerce_Cross_Seller/includes
 * @author     Cleverplugins.com <admin@cleverplugins.com>
 */
class Woocommerce_Cross_Seller_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'woocommerce-cross-seller',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
