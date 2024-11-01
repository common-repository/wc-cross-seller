<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://cleverplugins.com
 * @since      1.0.0
 *
 * @package    Woocommerce_Cross_Seller
 * @subpackage Woocommerce_Cross_Seller/public
 */
/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Woocommerce_Cross_Seller
 * @subpackage Woocommerce_Cross_Seller/public
 * @author     Cleverplugins.com <admin@cleverplugins.com>
 */
class Woocommerce_Cross_Seller_Public
{
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private  $plugin_name ;
    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private  $version ;
    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version )
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }
    
    /**
     * Adds ?wccs_unsub= parameter
     *
     * @since    1.0.0
     * @access   private
     */
    function add_query_vars_filter( $vars )
    {
        $vars[] = "wccs_unsub";
        return $vars;
    }
    
    /**
     * Checks if a customer wants to add themselves to the blocklist
     *
     * @since    1.0.0
     * @access   private
     */
    public function do_action_parse_query()
    {
        global  $wpdb, $woocommerce ;
        
        if ( $wccs_unsub = get_query_var( 'wccs_unsub' ) ) {
            // @todo - maybe not order key?
            $post_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_value = '%s' AND meta_key='_order_key';", $wccs_unsub ) );
            
            if ( isset( $post_id ) ) {
                $order = new WC_Order( $post_id );
                $_billing_email = $order->get_billing_email();
                $customer_email = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = '%d' AND meta_key='_billing_email';", $post_id ) );
                
                if ( is_email( $customer_email ) ) {
                    $wccs_settings = get_option( 'woocommerce_wc_customer_cross_sell_settings' );
                    $blocklist = $wccs_settings['blocklist'];
                    $blocklist_arr = explode( ',', $blocklist );
                    if ( is_array( $blocklist_arr ) ) {
                        
                        if ( $customer_email ) {
                            $blocklist_arr[] = $customer_email;
                            // add to the blocked list
                            $blocklist_arr = array_unique( $blocklist_arr );
                            // remove duplicates
                            $wccs_settings['blocklist'] = implode( ',', $blocklist_arr );
                            update_option( 'woocommerce_wc_customer_cross_sell_settings', $wccs_settings );
                        }
                    
                    }
                    $logger = wc_get_logger();
                    $logger->info( sprintf( __( '#%s - %s requested not to get any more emails and was added to the email blocklist.', 'woocommerce-cross-sell' ), $order->get_id(), $customer_email ), array(
                        'source' => 'wc-cross-sell',
                    ) );
                }
            
            }
        
        }
    
    }

}