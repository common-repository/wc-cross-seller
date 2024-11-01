<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://cleverplugins.com
 * @since      1.0.0
 *
 * @package    Woocommerce_Cross_Seller
 * @subpackage Woocommerce_Cross_Seller/admin
 */
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woocommerce_Cross_Seller
 * @subpackage Woocommerce_Cross_Seller/admin
 * @author     Cleverplugins.com <admin@cleverplugins.com>
 */
class Woocommerce_Cross_Seller_Admin
{
    private  $plugin_name ;
    private  $version ;
    public function __construct( $plugin_name, $version )
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }
    
    /**
     * Add the menu item under WooCommerce page
     * @author larsk
     */
    function add_menu_link()
    {
        add_submenu_page(
            'woocommerce',
            __( 'Cross Sell', 'woocommerce-cross-seller' ),
            __( 'Cross Sell', 'woocommerce-cross-seller' ),
            'manage_options',
            'admin.php?page=wc-settings&tab=email&section=wc_customer_cross_sell'
        );
    }
    
    /**
     * Monitors any change to WooCommerce orders and schedules or unschedules accordingly.
     * @author larsk
     * @param $order_id 		- Affected order id
     * @param $old_status 	- Previous status of the order
     * @param $new_status 	- Current status of the order
     * @since  1.0
     * @return void
     */
    function do_filter_woocommerce_order_status_changed( $order_id, $old_status, $new_status )
    {
        $wccs_settings = get_option( 'woocommerce_wc_customer_cross_sell_settings' );
        $orderstatusname = $wccs_settings['orderstatus'];
        $wc_get_order_statuses = wc_get_order_statuses();
        foreach ( $wc_get_order_statuses as $key => $status ) {
            if ( $key == 'wc-' . $old_status ) {
                $oldstatuskey = $key;
            }
            if ( $key == 'wc-' . $new_status ) {
                $newstatuskey = $key;
            }
        }
        
        if ( $newstatuskey == $orderstatusname ) {
            $order = new WC_Order( $order_id );
            $_billing_email = $order->get_billing_email();
            // for {customer_email}
            
            if ( !email_exists( $_billing_email ) ) {
                $logger = wc_get_logger();
                // Important info, so we debug no matter what
                $logger->warning( sprintf( __( '#%s no email found with order, no emails will be scheduled', 'woocommerce-cross-seller' ), $order_id ), array(
                    'source' => 'wc-cross-sell',
                ) );
                return;
                // Nevermind then...
            }
            
            
            if ( $this->is_debugging() ) {
                $logger = wc_get_logger();
                $logger->info( sprintf( __( '#%s marked as %s. Scheduling emails.', 'woocommerce-cross-seller' ), $order_id, wc_get_order_status_name( $orderstatusname ) ), array(
                    'source' => 'wc-cross-sell',
                ) );
            }
            
            // if ( $this->is_debugging() ) {
            $this->do_action_woocommerce_order_status_completed( $order_id );
        } else {
            $this->woocommerce_order_status_changed_remove_schedule( $order_id, $new_status );
        }
    
    }
    
    /**
     * Runs when an order is marked as complete
     * @author larsk
     * @param  int $order_id The id of the order
     * @return null
     */
    function do_action_woocommerce_order_status_completed( $order_id )
    {
        $wccs_settings = get_option( 'woocommerce_wc_customer_cross_sell_settings' );
        // Check if plugin is enabled
        if ( $wccs_settings['enabled'] != 'yes' ) {
            return $order_id;
        }
        $wccs_settings['excludeproducts'];
        $nosend_items = explode( ',', $wccs_settings['excludeproducts'] );
        
        if ( is_array( $nosend_items ) ) {
            //	$nosend_items = array('####', '####'); // replace hashes with product IDs to exclude
            $order = new WC_Order( $order_id );
            $items = $order->get_items();
            foreach ( $items as $item ) {
                
                if ( in_array( $item['product_id'], $nosend_items ) ) {
                    $logger = wc_get_logger();
                    // Important info
                    $logger->warning( sprintf( __( '#%s No scheduled emails to be sent due to exclusion of product ID # %s', 'woocommerce-cross-sell' ), $order_id, $item['product_id'] ), array(
                        'source' => 'wc-cross-sell',
                    ) );
                    return;
                }
            
            }
        }
        
        if ( $order_id != 0 ) {
            $order = new WC_Order( $order_id );
        }
        $_billing_email = $order->get_billing_email();
        // @todo metode der checker $_POST om det er en test forsendelse oder was..
        if ( !is_email( $_billing_email ) ) {
            // not an email? Nevermind then
            return false;
        }
        
        if ( $this->is_email_blocked( $_billing_email ) ) {
            // Important info, so we log it no matter what
            $logger = wc_get_logger();
            $logger->info( sprintf( __( '#%s has changed status, but %s has requested not to get any more review emails. Nothing scheduled.', 'woocommerce-cross-sell' ), $order_id, $_billing_email ), array(
                'source' => 'wc-cross-sell',
            ) );
            $order->add_order_note( sprintf( __( 'This order has changed, but %s has requested not to get any more review emails. Nothing scheduled.', 'woocommerce-cross-sell' ), $_billing_email ) );
            return $order_id;
        }
        
        $reminderdays = explode( ',', $wccs_settings['interval'] );
        
        if ( $reminderdays ) {
            $scheduleddays = '';
            if ( is_array( $reminderdays ) && $wccs_settings['interval'] != '' ) {
                foreach ( $reminderdays as $rd ) {
                    $args = array( $order_id, $rd, $_billing_email );
                    $futuretime = time() + $rd * 86400;
                    $scheduleresult = wp_schedule_single_event( $futuretime, 'woocommerce_cross_sell_send_email', $args );
                    $scheduleddays .= date_i18n( get_option( 'date_format' ), $futuretime ) . ', ';
                }
            }
            
            if ( $scheduleddays ) {
                
                if ( $this->is_debugging() ) {
                    $logger = wc_get_logger();
                    $logger->info( sprintf(
                        __( '#%s Scheduled emails to be sent to %s on following dates: %s', 'woocommerce-cross-sell' ),
                        $order_id,
                        $_billing_email,
                        $scheduleddays
                    ), array(
                        'source' => 'wc-cross-sell',
                    ) );
                }
                
                // if ( $this->is_debugging() ) {
            }
        
        }
    
    }
    
    /**
     * Returns true if we are debugging.
     * @author larsk
     * @since  1.0
     * @return null
     */
    function is_debugging()
    {
        $wccs_settings = get_option( 'woocommerce_wc_customer_cross_sell_settings' );
        if ( $wccs_settings['debuglog'] ) {
            return true;
        }
        return false;
    }
    
    /**
     * Checks if an email is already on the blocklist, returns true if email is blocked.
     * @author larsk
     * @since  1.0
     * @return null
     */
    function is_email_blocked( $emailtotest )
    {
        
        if ( !is_email( $emailtotest ) ) {
            return false;
            // this is not an email, so return false
        }
        
        $wccs_settings = get_option( 'woocommerce_wc_customer_cross_sell_settings' );
        $blocklist = $wccs_settings['blocklist'];
        $blocklist_arr = explode( ',', $blocklist );
        if ( in_array( $emailtotest, $blocklist_arr ) ) {
            // email found, so return true
            return true;
        }
        return false;
    }
    
    /**
     * Returns the url set to direct unsubscribers to
     * @author larsk
     * @since 1.0
     * @param  none
     * @return null
     */
    function return_stop_link()
    {
        $wccs_settings = get_option( 'woocommerce_wc_customer_cross_sell_settings' );
        if ( $wccs_settings['stoplink'] ) {
            return $wccs_settings['stoplink'];
        }
        return site_url();
        // Default if the link to the page after unsubscribing is not set.
    }
    
    /**
     * Sends email reminder
     * @author larsk
     * @param  int    $order_id 	Order ID to process - Use 0 as value to send dummy email and data
     * @param  int    $days		Number of days reminder
     * @param  string $email		Customers email
     * @return null
     */
    function do_action_send_email_reminder( $order_id, $days, $email )
    {
        global  $wpdb, $woocommerce, $wccrossseller_fs ;
        $wccs_settings = get_option( 'woocommerce_wc_customer_cross_sell_settings' );
        $total_emails_sent = get_option( 'woocommerce_cross_seller_total_emails_sent', 0 );
        // Check if plugin is enabled
        
        if ( $wccs_settings['enabled'] != 'yes' and $order_id != 0 ) {
            // skip if set to send test email
            $logger = wc_get_logger();
            $logger->info( __( 'Plugin deactivated, not sending emails.', 'woocommerce-cross-sell' ), array(
                'source' => 'wc-cross-sell',
            ) );
            wp_clear_scheduled_hook( 'woocommerce_cross_sell_send_email', array( $order_id, $days, $email ) );
            // Remove the current cron if not enabled.
            return;
        }
        
        
        if ( $this->is_email_blocked( $email ) ) {
            $logger = wc_get_logger();
            $logger->info( sprintf( __( '#%s - %s has requested not to get any more emails. Email was not sent.', 'woocommerce-cross-sell' ), $order_id, $email ), array(
                'source' => 'wc-cross-sell',
            ) );
            wp_clear_scheduled_hook( 'woocommerce_cross_sell_send_email', array( $order_id, $days, $email ) );
            // Remove the current cron.
            return;
        }
        
        $button_bg_color = $wccs_settings['buttonbg'];
        $buttoncolor = $wccs_settings['buttoncolor'];
        $buttontext = $wccs_settings['buttontext'];
        $order = new WC_Order( $order_id );
        $_completed_date = get_post_meta( $order_id, '_completed_date', true );
        $_completed_date = date_i18n( get_option( 'date_format' ), strtotime( $_completed_date ) );
        $_billing_email = $order->get_billing_email();
        // $order->billing_email;
        $_shipping_last_name = $order->get_billing_last_name();
        $_shipping_first_name = $order->get_billing_first_name();
        $_order_key = $order->get_order_key();
        $order_date = $order->get_date_created();
        // for {order_date}
        $blacklist_link = add_query_arg( array(
            'wccs_unsub' => $_order_key,
        ), $this->return_stop_link() );
        // for {blacklist_link}
        $stoplink = '<a href="' . $blacklist_link . '">' . $wccs_settings['stoptext'] . '</a>';
        // for {unsubscribe_link}
        $customeremail = $order->get_billing_email();
        if ( !$customeremail ) {
            return false;
        }
        // @todo - better notification
        global  $wpdb ;
        $wccs_settings = get_option( 'woocommerce_wc_customer_cross_sell_settings' );
        $recommended_headline = $wccs_settings['recommended_headline'];
        // ref: https://businessbloomer.com/woocommerce-display-products-purchased-user/ @todo credit
        $purchased_products_ids = $wpdb->get_col( $wpdb->prepare( "SELECT      itemmeta.meta_value\n\t\t\tFROM        {$wpdb->prefix}woocommerce_order_itemmeta itemmeta\n\t\t\tINNER JOIN  {$wpdb->prefix}woocommerce_order_items items\n\t\t\tON itemmeta.order_item_id = items.order_item_id\n\t\t\tINNER JOIN  {$wpdb->posts} orders\n\t\t\tON orders.ID = items.order_id\n\t\t\tINNER JOIN  {$wpdb->postmeta} ordermeta\n\t\t\tON orders.ID = ordermeta.post_id\n\t\t\tWHERE       itemmeta.meta_key = '_product_id'\n\t\t\tAND ordermeta.meta_key = '_billing_email'\n\t\t\tAND ordermeta.meta_value = %s\n\t\t\tORDER BY    orders.post_date DESC\n\t\t\t", $customeremail ) );
        $excludeproducts = explode( $wccs_settings['excludeproducts'], ',' );
        // Merge in to one array
        $post__not_in = array_merge( $excludeproducts, $purchased_products_ids );
        $post__not_in = array_unique( $post__not_in );
        $product_suggestions = new WP_Query( array(
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'orderby'        => 'rand',
            'order'          => 'DESC',
            'posts_per_page' => 3,
            'post__not_in'   => $post__not_in,
        ) );
        $_style_tdstyle = 'text-align:left; vertical-align:top; word-wrap:break-word;';
        $productsoutput = '';
        
        if ( $product_suggestions->have_posts() ) {
            $product_names = '';
            $productsoutput = '<table>';
            while ( $product_suggestions->have_posts() ) {
                $product_suggestions->the_post();
                //$prodid = $post->get_id();
                $prodid = get_the_ID();
                $product = new WC_Product( $prodid );
                $button = '<a href="' . get_permalink( $prodid ) . $wccs_settings['urlappend'] . '" target="_blank" style="font-size: 16px; font-family: Helvetica, Arial, sans-serif; color: ' . $buttoncolor . '; text-decoration: none; border-radius: 3px; -webkit-border-radius: 3px; -moz-border-radius: 3px; background-color: ' . $button_bg_color . '; border-top: 12px solid ' . $button_bg_color . '; border-bottom: 12px solid ' . $button_bg_color . '; border-right: 18px solid ' . $button_bg_color . '; border-left: 18px solid ' . $button_bg_color . '; display: inline-block;">' . $buttontext . '</a>';
                $_style_tdstyle = 'padding:12px 12px 12px 0px;text-align:left; vertical-align:top; word-wrap:break-word;';
                $src = wp_get_attachment_image_src( get_post_thumbnail_id( $prodid ), array( 150, 150 ) );
                $image = '';
                $image = get_the_post_thumbnail( $product->get_id(), apply_filters( 'single_product_large_thumbnail_size', array( 150, 150 ) ) );
                $image_title = esc_attr( get_the_title( get_post_thumbnail_id() ) );
                $image_link = wp_get_attachment_url( get_post_thumbnail_id() );
                
                if ( is_array( $src ) ) {
                    $imagehtml = apply_filters( 'woocommerce_order_product_image', '<img src="' . $src[0] . '" alt="' . $product->get_title() . '" height="150" width="150" style="vertical-align:middle; margin-right: 10px;" />', $product );
                } else {
                    $imagehtml = apply_filters( 'woocommerce_single_product_image_html', sprintf( '<img src="%s" alt="' . $product->get_title() . '" height="150" width="150" style="vertical-align:middle; margin-right: 10px;" />', wc_placeholder_img_src() ), $product->ID );
                }
                
                $productsoutput .= '<tr>';
                $productsoutput .= '<td style="' . $_style_tdstyle . '">' . $imagehtml . '</td><td style="' . $_style_tdstyle . '"><h3 style="margin-top:0px;">' . $product->get_title() . '</h3>';
                $product_names .= $product->get_title() . ', ';
                $productsoutput .= wp_trim_words( apply_filters( 'the_content', $product->get_short_description() ), 42 );
                $productsoutput .= '<p>' . $product->get_price_html() . '</p>';
                $productsoutput .= $button;
                $productsoutput .= '</td></tr>';
            }
            $product_names = rtrim( $product_names, ', ' );
            // removing the last comma,
            $productsoutput .= '</table>';
        }
        
        wp_reset_postdata();
        $replace_list = array();
        $replace_list['{products}'] = $productsoutput;
        $replace_list['{product_names}'] = $product_names;
        $replace_list['{customer_name}'] = $_shipping_first_name . ' ' . $_shipping_last_name;
        $replace_list['{customer_firstname}'] = $_shipping_first_name;
        $replace_list['{customer_lastname}'] = $_shipping_last_name;
        $replace_list['{order_id}'] = $order_id;
        $saved_order_id = get_post_meta( $order_id, '_order_number', true );
        // Gets the real order numer if different from post id
        if ( $saved_order_id != $order_id ) {
            $replace_list['{order_id}'] = $saved_order_id;
        }
        $replace_list['{today_date}'] = date_i18n( get_option( 'date_format' ), current_time( 'timestamp' ) );
        $replace_list['{customer_email}'] = $email;
        $replace_list['{order_date}'] = date_i18n( get_option( 'date_format' ), strtotime( $order_date ) );
        $replace_list['{order_date_completed}'] = $_completed_date;
        $replace_list['{blacklist_link}'] = $blacklist_link;
        $replace_list['{days_ago}'] = $days;
        $replace_list['{site_title}'] = get_bloginfo( 'name' );
        $replace_list['{unsubscribe_link}'] = $stoplink;
        $subject_line = $wccs_settings['subject'];
        $emailheadline = $wccs_settings['emailheadline'];
        $mailer = $woocommerce->mailer();
        $template = 'emails/wc-customer-cross-sell.php';
        $full_message = wc_get_template_html(
            $template,
            array(
            'order'         => $order,
            'email_heading' => $emailheadline,
            'sent_to_admin' => false,
            'plain_text'    => false,
            'email'         => $mailer,
        ),
            WC_CROSS_SELL_DIR_PATH . 'templates/',
            WC_CROSS_SELL_DIR_PATH . 'templates/'
        );
        foreach ( $replace_list as $searchfor => $replacewith ) {
            $full_message = str_replace( $searchfor, stripslashes( $replacewith ), $full_message );
            $subject_line = str_replace( $searchfor, stripslashes( $replacewith ), $subject_line );
            $emailheadline = str_replace( $searchfor, stripslashes( $replacewith ), $emailheadline );
        }
        // @todo - mangler jeg nogle headers?
        $headers = 'Content-type: text/html;charset=utf-8' . "\r\n";
        $mailer->send(
            $email,
            $subject_line,
            $full_message,
            $headers
        );
        $total_emails_sent++;
        update_option( 'woocommerce_cross_seller_total_emails_sent', $total_emails_sent );
        $logger = wc_get_logger();
        $logger->info( sprintf(
            __( '#%s E-mail sent to %s - %s days after purchase.', 'woocommerce-cross-sell' ),
            $order_id,
            $email,
            $days
        ), array(
            'source' => 'wc-cross-sell',
        ) );
        $order->add_order_note( sprintf( __( 'Product suggestions was emailed by WooCommerce Cross Seller. %s days after purchase.', 'woocommerce-cross-sell' ), $days ) );
        // Cleaning up after ourselves
        wp_clear_scheduled_hook( 'woocommerce_cross_sell_send_email', array( $order_id, $days, $email ) );
    }
    
    /**
     * Runs on several woocommerce do_action() to remove any orders
     * @author larsk
     * @param  int $order_id The unique order id
     * @return null
     */
    function woocommerce_order_status_changed_remove_schedule( $order_id, $new_status )
    {
        $crons = _get_cron_array();
        $hook = 'woocommerce_cross_sell_send_email';
        foreach ( $crons as $timestamp => $cron ) {
            if ( isset( $cron[$hook] ) ) {
                if ( is_array( $cron[$hook] ) ) {
                    foreach ( $cron[$hook] as $details ) {
                        $target = $details['args'][0];
                        // order id from paramaters
                        if ( $target == $order_id ) {
                            // unset this scheduled event
                            unset( $crons[$timestamp][$hook] );
                        }
                    }
                }
            }
        }
        $logger = wc_get_logger();
        $logger->info( sprintf( __( 'Order #%s changed status to %s, Any emails scheduled to be sent has been deleted.', 'woocommerce-cross-seller' ), $order_id, wc_get_order_status_name( $new_status ) ), array(
            'source' => 'wc-cross-sell',
        ) );
        _set_cron_array( $crons );
    }
    
    /**
     * Process on save post (order) - send email requests immediately
     * @author larsk
     * @since  1.4
     * @return null
     */
    function do_filter_save_post( $post_id, $post )
    {
        if ( !isset( $_POST['send_reminder_now'] ) ) {
            // we are not going to do anything, since the send button has not been clicked.
            return $post_id;
        }
        if ( !wp_verify_nonce( $_POST['wcarr_send_immediately'], plugin_basename( __FILE__ ) ) ) {
            return $post_id;
        }
        if ( !current_user_can( 'edit_post', $post->ID ) ) {
            return $post_id;
        }
        // Look up billing email
        
        if ( $_POST['send_reminder_now'] ) {
            $_billing_email = get_post_meta( $post_id, '_billing_email', true );
            
            if ( $this->is_email_blocked( $_billing_email ) ) {
                // user already on blocklist
                $logger = wc_get_logger();
                $logger->info( sprintf( __( 'Manual review reminder - %s has requested not to get any more review emails. Email was not sent.', 'woocommerce-cross-seller' ), $post_id, $_billing_email ), array(
                    'source' => 'wc-cross-sell',
                ) );
                return $post_id;
            } else {
                $this->send_email_reminder( $post_id, 0, $_billing_email );
            }
        
        }
    
    }
    
    /**
     * Registers notifications, welcome message etc.
     *
     * @since 1.0.0
     * @param array $emails
     *
     * @return array
     */
    public function do_admin_notices()
    {
        $total_emails_sent = get_option( 'woocommerce_cross_seller_total_emails_sent', 0 );
        // Shows welcome notice if still active
        
        if ( PAnD::is_admin_notice_active( 'wccs-welcome-forever' ) ) {
            $directlink = add_query_arg( array(
                'page'    => 'wc-settings',
                'tab'     => 'email',
                'section' => 'wc_customer_cross_sell',
            ), admin_url( 'admin.php' ) );
            ?>
			<div data-dismissible="wccs-welcome-forever" class="updated notice notice-success is-dismissible">
				<div class="cp_logo"><a href="https://cleverplugins.com/" target="_blank"><img src='<?php 
            echo  WC_CROSS_SELL_DIR_URL ;
            ?>images/cleverpluginslogo.png' width="220" alt="<?php 
            _e( 'Visit cleverplugins.com', 'seo-booster' );
            ?>"></a></div>
				<h2>WooCommerce Cross Seller</h2>
				<p><?php 
            _e( 'Thank you for installing. The plugin is now active. New customers with completed purchases will get product suggestions via email.', 'woocommerce-cross-seller' );
            ?></p>
				<p><?php 
            _e( 'The plugin can do more, go take a look at the settings.', 'woocommerce-cross-seller' );
            ?></p>
				<p><?php 
            echo  '<a href="' . esc_url( $directlink ) . '" class="visitsettings button-secondary">' . __( 'Click here to visit the settings', 'woocommerce-cross-seller' ) . '</a>' ;
            ?></p>

			</div>
			<?php 
        }
        
        // if ( PAnD::is_admin_notice_active( 'wccs-welcome-forever' ) )
        // Shows after 100
        
        if ( PAnD::is_admin_notice_active( 'wccs-100-forever' ) && ($total_emails_sent > 100 && $total_emails_sent < 1000) ) {
            ?>

			<div data-dismissible="wccs-100-forever" class="notice notice-success is-dismissible">
				<div class="cp_logo"><a href="https://cleverplugins.com/" target="_blank"><img src='<?php 
            echo  WC_CROSS_SELL_DIR_URL ;
            ?>images/cleverpluginslogo.png' width="220" alt="<?php 
            _e( 'Visit cleverplugins.com', 'seo-booster' );
            ?>"></a></div>
				<h2><?php 
            _e( 'Over 100 emails sent!', 'woocommerce-cross-seller' );
            ?></h2>
				<p class="lead"><?php 
            printf( esc_html__( 'Hey, I noticed WooCommerce Cross Seller has sent %d emails – that’s awesome!', 'woocommerce-cross-seller' ), $total_emails_sent );
            ?></p>

				<p><?php 
            _e( ' Could you please do me a BIG favor and give it a 5-star rating on WordPress? Just to help us spread the word and boost our motivation :-)', 'woocommerce-cross-seller' );
            ?></p>
				<p>Lars Koudal,</br>cleverplugins.com</p>
				<ul id="noticeoptions">
					<li><span class="dashicons dashicons-heart"></span> <a href="https://wordpress.org/support/plugin/wc-cross-seller/reviews/?filter=5#new-post" target="_blank"><?php 
            _e( 'Ok, you deserve it', 'woocommerce-cross-seller' );
            ?></a></li>
				</ul>
			</div>
			<?php 
        }
        
        // if ( PAnD::is_admin_notice_active( 'wc-cross-sell-100-sent' ) )
    }
    
    /**
     * Adds a direct link to settings from plugin overview page.
     *
     * @since    1.0.0
     *
     */
    function add_settings_link( $actions, $plugin_file )
    {
        static  $plugin ;
        if ( !isset( $plugin ) ) {
            $plugin = plugin_basename( __FILE__ );
        }
        
        if ( $plugin == 'woocommerce-cross-seller/class-woocommerce-cross-seller-admin.php' ) {
            $settings = array(
                'settings' => '<a href="' . esc_url( $directlink ) . '">' . __( 'Settings', 'woocommerce-cross-seller' ) . '</a>',
            );
            $documentation = array(
                'documentation' => '<a href="https://cleverplugins.com/support/" target="_blank">' . __( 'Support', 'woocommerce-cross-seller' ) . '</a>',
            );
            $actions = array_merge( $settings, $actions, $documentation );
        }
        
        return $actions;
    }
    
    /**
     * Register the email class with WC
     *
     * @since    1.0.0
     * @param array $emails
     *
     * @return array
     */
    public function register_wc_email( $emails )
    {
        require_once WC_CROSS_SELL_DIR_PATH . 'includes/class-wc-customer-cross-sell.php';
        $emails['WC_Customer_Cross_Sell'] = new WC_Customer_Cross_Sell();
        return $emails;
    }
    
    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        wp_enqueue_style(
            $this->plugin_name,
            plugin_dir_url( __FILE__ ) . 'css/woocommerce-cross-seller-admin.css',
            array(),
            $this->version,
            'all'
        );
    }

}