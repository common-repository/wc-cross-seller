<?php

/**
 * Plugin Name:       WooCommerce Cross-Seller
 * Plugin URI:        https://cleverplugins.com/woocommerce-cross-seller/
 * Description:       Keep customers engaged and get more sales with product recommendations. Automatically emails customers.
 * Version:           1.0.5
 * Author:            Cleverplugins.com
 * Author URI:        https://cleverplugins.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woocommerce-cross-seller
 * Domain Path:       /languages
 * WC requires at least: 3.3
 * WC tested up to: 3.5.5
 */
/*
Uses
Persist Admin notice Dismissals - https://github.com/collizo4sky/persist-admin-notices-dismissal
TODO
- Lav til en setting hvor mange produkter der udsendes.
- Lav test funktionalitet - check den udkommenterede kode fungerer korrekt.
*/
// If this file is called directly, abort.
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if ( function_exists( 'wccrossseller_fs' ) ) {
    wccrossseller_fs()->set_basename( false, __FILE__ );
    return;
}

define( 'WC_CROSS_SELL_VERSION', '1.0.5' );
define( 'WC_CROSS_SELL_DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'WC_CROSS_SELL_DIR_URL', plugin_dir_url( __FILE__ ) );

if ( !function_exists( 'wccrossseller_fs' ) ) {
    // Create a helper function for easy SDK access.
    function wccrossseller_fs()
    {
        global  $wccrossseller_fs ;
        
        if ( !isset( $wccrossseller_fs ) ) {
            // Include Freemius SDK.
            require_once dirname( __FILE__ ) . '/freemius/start.php';
            $wccrossseller_fs = fs_dynamic_init( array(
                'id'             => '3017',
                'slug'           => 'wc-cross-seller',
                'type'           => 'plugin',
                'public_key'     => 'pk_204a19b41c06948ad7a4cce236742',
                'is_premium'     => false,
                'premium_suffix' => 'Premium',
                'has_addons'     => false,
                'has_paid_plans' => true,
                'trial'          => array(
                'days'               => 14,
                'is_require_payment' => false,
            ),
                'menu'           => array(
                'first-path' => 'plugins.php',
                'support'    => false,
            ),
                'is_live'        => true,
            ) );
        }
        
        return $wccrossseller_fs;
    }
    
    // Init Freemius.
    wccrossseller_fs();
    // Signal that SDK was initiated.
    do_action( 'wccrossseller_fs_loaded' );
}

function wccrossseller_fs_uninstall_cleanup()
{
    delete_option( 'woocommerce_cross_seller_total_emails_sent' );
    delete_option( 'pand-' . md5( 'wccs-welcome-forever' ) );
    delete_option( 'pand-' . md5( 'wccs-100-forever' ) );
}

/**
 * Custom icon for the plugin - override Freemius
 *
 * @since    1.0.0
 */
function wccrossseller_fs_custom_icon()
{
    return dirname( __FILE__ ) . '/images/woocommerce-cross-seller-logo.svg';
}

wccrossseller_fs()->add_filter( 'plugin_icon', 'wccrossseller_fs_custom_icon' );
/**
 * Check if WooCommerce is active
 **/
if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    wp_die( __( 'WooCommerce not installed or active', 'woocommerce-cross-seller' ) );
}
function activate_woocommerce_cross_seller()
{
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-cross-seller-activator.php';
    Woocommerce_Cross_Seller_Activator::activate();
}

register_activation_hook( __FILE__, 'activate_woocommerce_cross_seller' );
require plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-cross-seller.php';
function run_woocommerce_cross_seller()
{
    $plugin = new Woocommerce_Cross_Seller();
    $plugin->run();
}

run_woocommerce_cross_seller();