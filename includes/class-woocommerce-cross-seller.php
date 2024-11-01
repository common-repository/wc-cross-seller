<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://cleverplugins.com
 * @since      1.0.0
 *
 * @package    Woocommerce_Cross_Seller
 * @subpackage Woocommerce_Cross_Seller/includes
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
 * @package    Woocommerce_Cross_Seller
 * @subpackage Woocommerce_Cross_Seller/includes
 * @author     Cleverplugins.com <admin@cleverplugins.com>
 */
class Woocommerce_Cross_Seller
{
    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Woocommerce_Cross_Seller_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected  $loader ;
    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected  $plugin_name ;
    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected  $version ;
    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct()
    {
        
        if ( defined( 'PLUGIN_NAME_VERSION' ) ) {
            $this->version = PLUGIN_NAME_VERSION;
        } else {
            $this->version = '1.0.1';
        }
        
        $this->plugin_name = 'woocommerce-cross-seller';
        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }
    
    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Woocommerce_Cross_Seller_Loader. Orchestrates the hooks of the plugin.
     * - Woocommerce_Cross_Seller_i18n. Defines internationalization functionality.
     * - Woocommerce_Cross_Seller_Admin. Defines all hooks for the admin area.
     * - Woocommerce_Cross_Seller_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies()
    {
        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woocommerce-cross-seller-loader.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woocommerce-cross-seller-i18n.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-woocommerce-cross-seller-admin.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-woocommerce-cross-seller-public.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'vendor/persist-admin-notices-dismissal/persist-admin-notices-dismissal.php';
        add_action( 'admin_init', array( 'PAnD', 'init' ) );
        $this->loader = new Woocommerce_Cross_Seller_Loader();
    }
    
    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Woocommerce_Cross_Seller_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale()
    {
        $plugin_i18n = new Woocommerce_Cross_Seller_i18n();
        $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
    }
    
    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks()
    {
        $plugin_admin = new Woocommerce_Cross_Seller_Admin( $this->get_plugin_name(), $this->get_version() );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        // Registers new WC email class
        $this->loader->add_action( 'woocommerce_email_classes', $plugin_admin, 'register_wc_email' );
        // Register welcome notification
        $this->loader->add_action( 'admin_notices', $plugin_admin, 'do_admin_notices' );
        // Adds the menu page under WooCommerce
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_menu_link' );
        $this->loader->add_action(
            'woocommerce_cross_sell_send_email',
            $plugin_admin,
            'do_action_send_email_reminder',
            10,
            3
        );
        $this->loader->add_filter(
            'plugin_action_links',
            $plugin_admin,
            'add_settings_link',
            10,
            5
        );
        // Checks every time an order changes status if it is time to schedule the emails
        $this->loader->add_filter(
            'woocommerce_order_status_changed',
            $plugin_admin,
            'do_filter_woocommerce_order_status_changed',
            10,
            5
        );
        $this->loader->add_filter(
            'save_post',
            $plugin_admin,
            'do_filter_save_post',
            1,
            2
        );
    }
    
    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks()
    {
        $plugin_public = new Woocommerce_Cross_Seller_Public( $this->get_plugin_name(), $this->get_version() );
        // Allow us to use wccs_unsub= as paramater, please.
        $this->loader->add_filter( 'query_vars', $plugin_public, 'add_query_vars_filter' );
        // Check if wccs_unsub= set as a param and unsubscribe if necessary
        $this->loader->add_action( 'parse_query', $plugin_public, 'do_action_parse_query' );
    }
    
    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run()
    {
        $this->loader->run();
    }
    
    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }
    
    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Woocommerce_Cross_Seller_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader()
    {
        return $this->loader;
    }
    
    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version()
    {
        return $this->version;
    }

}