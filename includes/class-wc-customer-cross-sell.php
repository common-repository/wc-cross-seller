<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly
}

if ( !class_exists( 'WC_Email' ) ) {
    return;
}
/**
 * Class WC_Customer_Cross_Sell
 */
class WC_Customer_Cross_Sell extends WC_Email
{
    function __construct()
    {
        // Email slug we can use to filter other data.
        $this->id = 'wc_customer_cross_sell';
        $this->title = __( 'Customer Cross Sell', 'woocommerce-cross-seller' );
        $this->description = __( 'An email sent to the customer x days after any purchase, promoting other products in your store.', 'woocommerce-cross-seller' );
        // For admin area to let the user know we are sending this email to customers.
        $this->customer_email = true;
        // Template paths.
        $this->template_html = 'emails/wc-customer-cross-sell.php';
        $this->template_base = WC_CROSS_SELL_DIR_PATH . 'templates/';
        parent::__construct();
    }
    
    /**
     * Adds custom HTML to settings page.
     *
     * @since 1.0.0
     * @author larsk
     * @return void
     */
    public function admin_options()
    {
        global  $wccrossseller_fs ;
        
        if ( !$wccrossseller_fs->is_registered() && !$wccrossseller_fs->is_pending_activation() ) {
            // Website is not registered
            ?>
				<div class="notice notice-info">
					<h2>WooCommerce Cross Seller</h2>
					<p><?php 
            echo  sprintf( __( 'Never miss an important update. Opt-in to our security and feature updates notifications, and non-sensitive diagnostic tracking with freemius.com. <a href="%s">Click here to opt in.</a>', 'seo-booster' ), $wccrossseller_fs->get_reconnect_url() ) ;
            ?></p>
				</div>
				<?php 
        }
        
        
        if ( $wccrossseller_fs->is_pending_activation() && !$wccrossseller_fs->is_registered() ) {
            ?>
					<div class="notice notice-info">
						<h2>WooCommerce Cross Seller</h2>
						<p><?php 
            _e( 'Thank you for activating, please check your email to complete the process.', 'seo-booster' );
            ?></p>
					</div>
					<?php 
        }
        
        
        if ( $wccrossseller_fs->is_registered() ) {
            ?>
					<div class="notice notice-info">
						<h2>WooCommerce Cross Seller</h2>
						<?php 
            $fsuserdetails = $wccrossseller_fs->get_user();
            $total_emails_sent = get_option( 'woocommerce_cross_seller_total_emails_sent' );
            if ( $total_emails_sent > 0 ) {
                echo  '<p class="lead">' . sprintf( esc_html( _n(
                    'This plugin has sent %d email so far.',
                    'This plugin has sent %d emails so far.',
                    $total_emails_sent,
                    'woocommerce-cross-seller'
                ) ), $total_emails_sent ) . '</p>' ;
            }
            ?>
						<table id="wccssettings_table">
							<tr>
								<td>
									<h4><span class="dashicons dashicons-businessman"></span> <?php 
            _e( 'Account Management', 'woocommerce-cross-seller' );
            ?></h4>
									<p><?php 
            echo  '<a href="' . $wccrossseller_fs->get_account_url() . '">' . __( 'Account Details', 'woocommerce-cross-seller' ) . '</a>' ;
            ?></br>
									<small><?php 
            _e( 'Manage your account.', 'woocommerce-cross-seller' );
            ?></small></p>
									<p><?php 
            echo  '<a href="' . $wccrossseller_fs->get_upgrade_url() . '">' . __( 'Plans and Pricing', 'woocommerce-cross-seller' ) . '</a>' ;
            ?></br>
									<small><?php 
            _e( 'Upgrade or downgrade your subscription.', 'woocommerce-cross-seller' );
            ?></small>
								</p>
							</td>
							<td>
								<h4><span class="dashicons dashicons-welcome-learn-more"></span> <?php 
            _e( 'Help and support', 'woocommerce-cross-seller' );
            ?></h4>
								<p><?php 
            echo  '<a href="https://support.cleverplugins.com/collection/36-woocommerce-cross-seller" target="_blank">' . __( 'Knowledge base', 'woocommerce-cross-seller' ) . '</a>' ;
            ?></br>
								<small><?php 
            _e( 'Visit the Knowledge Base.', 'woocommerce-cross-seller' );
            ?></small>

							</p>
							<p><?php 
            echo  '<a href="' . $wccrossseller_fs->contact_url() . '" target="_blank">' . __( 'Contact Support', 'woocommerce-cross-seller' ) . '</a>' ;
            ?>
						</br>
						<small><?php 
            _e( 'Have a question? Use the contact page.', 'woocommerce-cross-seller' );
            ?></small>
					</p>
				</td>
				<td>
					<h4><span class="dashicons dashicons-admin-links"></span> <?php 
            _e( 'Helpful links', 'woocommerce-cross-seller' );
            ?></h4>
					<ul>
						<li><a href="https://www.emojicopy.com/" target="_blank">emojicopy.com</a></br>
							<small><?php 
            _e( 'Emojis can spice up your emails. Careful not to overdo it.', 'woocommerce-cross-seller' );
            ?></small>
						</li>
						<li><a href="https://wordpress.org/plugins/email-log/" target="_blank">Email Log plugin</a></br>
							<small><?php 
            _e( 'To have a log of all outgoing emails from your website.', 'woocommerce-cross-seller' );
            ?></small>
						</li>
						<li><a href="https://wordpress.org/plugins/wp-crontrol/" target="_blank">WP Crontrol</a></br>
							<small><?php 
            _e( 'WP Crontrol lets you view and control what’s happening in the WP-Cron system.', 'woocommerce-cross-seller' );
            ?></small>
						</li>
					</ul>
				</td>
			</tr>
		</table>
	</div>
	<?php 
        }
        
        
        if ( !$wccrossseller_fs->can_use_premium_code() ) {
            ?>
	<div class="notice notice-success">
		<h2><span class="dashicons dashicons-star-filled"></span> <?php 
            _e( 'Do more with the pro version', 'woocommerce-cross-seller' );
            ?> <span class="dashicons dashicons-star-filled"></span></h2>
		<ul class="profeatures">
			<li><?php 
            _e( 'Edit email template directly in settings', 'woocommerce-cross-seller' );
            ?></br>
				<small><?php 
            _e( 'No need to edit the php template files, use the built-in email editor. Use macros to personalize your emails.', 'woocommerce-cross-seller' );
            ?></small>
			</li>
			<li><?php 
            _e( 'Show products on Thank You page after purchase.', 'woocommerce-cross-seller' );
            ?></li>
			<li><?php 
            _e( 'Multiple subject lines.', 'woocommerce-cross-seller' );
            ?></br>
				<small><?php 
            _e( 'Rotate between different email subject lines for your customers.', 'woocommerce-cross-seller' );
            ?></small>
			</li>
			<li><?php 
            _e( 'Support the author behind the plugin :-)', 'woocommerce-cross-seller' );
            ?></li>
			<li><a href="https://cleverplugins.com/woocommerce-cross-seller/" target="_blank"><?php 
            _e( 'Read more on cleverplugins.com', 'woocommerce-cross-seller' );
            ?></a></li>
		</ul>

		<?php 
            $upgradeurl = $wccrossseller_fs->get_upgrade_url();
            $trialurl = add_query_arg( array(
                'trial' => 'true',
            ), $upgradeurl );
            ?>
		<p class="buttonrow"> <a href="<?php 
            echo  $trialurl ;
            ?>" class="button-primary"><?php 
            _e( 'Free Trial - Try for Free 14 days', 'woocommerce-cross-seller' );
            ?></a></p>
	</div>
	<?php 
        }
        
        ?>


<h2><?php 
        echo  esc_html( $this->get_title() ) ;
        ?> <?php 
        wc_back_link( __( 'Return to emails', 'woocommerce' ), admin_url( 'admin.php?page=wc-settings&tab=email' ) );
        ?></h2>
<p><?php 
        _e( 'Customers are emailed product suggestions based on their purchases.', 'woocommerce-cross-seller' );
        ?></p>
<table class="form-table">
	<?php 
        $this->generate_settings_html();
        ?>
</table>

<?php 
        // Removed temporarily until verify works correctly
        /*
        <h3><?php _e('Send Test Email', 'woocommerce-cross-seller');?></h3>
        <?php wp_nonce_field('arr_nonce');?>
        <table class="form-table">
        	<tbody>
        		<tr valign="top">
        			<th scope="row" class="titledesc">
        				<label for="arr_email_recipient"><?php _e('Email Recipient', 'woocommerce-cross-seller');?></label>
        			</th>
        			<td class="forminp">
        				<fieldset>
        					<legend class="screen-reader-text"><span><?php _e('Email Recipient', 'woocommerce-cross-seller');?></span></legend>
        					<input class="input-text regular-input" type="text" name="arr_email_recipient" id="arr_email_recipient"  value="">
        					<p class="description"><?php _e('Enter a valid email to send a test email.', 'woocommerce-cross-seller');?><br />
        					</p>
        				</fieldset>
        			</td>
        		</tr>
        	</tbody>
        </table>
        <?php
        */
        $crons = _get_cron_array();
        $hook = 'woocommerce_cross_sell_send_email';
        
        if ( $crons ) {
            echo  "<h3>" . __( 'Scheduled Emails', 'woocommerce-cross-seller' ) . "</h3>" ;
            ?>
	<table class="wp-list-table widefat logtable">
		<thead>
			<tr>
				<th scope="shortcol" class="shortcol"><?php 
            _e( 'Customer', 'woocommerce-cross-seller' );
            ?></th>
				<th scope="col"><?php 
            _e( 'Order ID', 'woocommerce-cross-seller' );
            ?></th>
				<th scope="col"><?php 
            _e( 'Email', 'woocommerce-cross-seller' );
            ?></th>
				<th scope="col"><?php 
            _e( 'Scheduled', 'woocommerce-cross-seller' );
            ?></th>
			</tr>
		</thead>
		<tbody>
			<?php 
            $totalscheduled = 0;
            foreach ( $crons as $timestamp => $cron ) {
                
                if ( isset( $cron[$hook] ) and is_array( $cron[$hook] ) ) {
                    $details = $cron[$hook];
                    foreach ( $details as $key => $detail ) {
                        $order_id = $detail['args'][0];
                        $days = $detail['args'][1];
                        $user = $detail['args'][2];
                    }
                    echo  '<tr><td>' . $user . '</td><td>' . $order_id . '</td><td>' ;
                    echo  sprintf( __( '%d days after purchase', 'seo-booster' ), date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timestamp ) ) ;
                    echo  '</td></tr>' ;
                    $totalscheduled++;
                }
            
            }
            ?>
		</tbody>
	</table>
	<?php 
            printf( __( '%s emails scheduled.', 'woocommerce-cross-sell' ), $totalscheduled );
        }
        
        // if ($crons)
    }
    
    /**
     * Initialize Settings Form Fields
     *
     * @since 1.0
     */
    public function init_form_fields()
    {
        global  $wccrossseller_fs ;
        $macrolisttable = '<p class="description">' . __( 'You can use these macros to customize the content of the email', 'woocommerce-cross-seller' ) . '</p>';
        $macrolisttable .= '<table id="macrolist">';
        $macrolisttable .= '<tr><td><code>{customer_name}</code></td><td>' . __( 'Replaced with the customers name.', 'woocommerce-cross-seller' ) . '</td></tr>';
        $macrolisttable .= '<tr><td><code>{customer_firstname}</code></td><td>' . __( 'Replaced with the customers name.', 'woocommerce-cross-seller' ) . '</td></tr>';
        $macrolisttable .= '<tr><td><code>{customer_lastname}</code></td><td>' . __( 'Replaced with the customers name.', 'woocommerce-cross-seller' ) . '</td></tr>';
        $macrolisttable .= '<tr><td><code>{customer_email}</code></td><td>' . __( 'Replaced with the customer email.', 'woocommerce-cross-seller' ) . '</td></tr>';
        $macrolisttable .= '<tr><td><code>{site_title}</code></td><td>' . __( 'Replaced with the site title.', 'woocommerce-cross-seller' ) . '</td></tr>';
        $macrolisttable .= '<tr><td><code>{order_id}</code></td><td>' . __( 'Replaced with the order id.', 'woocommerce-cross-seller' ) . '</td></tr>';
        $macrolisttable .= '<tr><td><code>{order_date}</code></td><td>' . __( 'Replaced with the date and time of the order.', 'woocommerce-cross-seller' ) . '</td></tr>';
        $macrolisttable .= '<tr><td><code>{days_ago}</code></td><td>' . __( 'Replaced with the number of days ago the order was made.', 'woocommerce-cross-seller' ) . '</td></tr>';
        $macrolisttable .= '<tr><td><code>{today_date}</code></td><td>' . __( 'Replaced with todays date, no timestamp.', 'woocommerce-cross-seller' ) . '</td></tr>';
        $macrolisttable .= '<tr><td><code>{order_date_completed}</code></td><td>' . __( 'Replaced with the date the order was marked completed.', 'woocommerce-cross-seller' ) . '</td></tr>';
        $macrolisttable .= '<tr><td><code>{unsubscribe_link}</code></td><td>' . __( 'Replaced with a link to stop recieving product suggetions.', 'woocommerce-cross-seller' ) . '</td></tr>';
        $macrolisttable .= '<tr><td><code>{products}</code></td><td>' . __( 'Replaced with a table with the suggested products and direct links.', 'woocommerce-cross-seller' ) . '</td></tr>';
        $macrolisttable .= '<tr><td><code>{product_names}</code></td><td>' . __( 'Replaced with the suggested product names in a string format separated by a comma. "Product 1, product 2, ".. etc. ', 'woocommerce-cross-seller' ) . '</td></tr>';
        $macrolisttable .= '</table>';
        $macroliststring = ' ' . __( 'Available macros:', 'woocommerce-cross-seller' ) . ' {customer_name}, {customer_firstname}, {customer_lastname}, {customer_email}, {site_title}, {order_id}, {order_date}, {days_ago}, {today_date}, {products}, {product_names}, {order_date_completed}, {unsubscribe_link}';
        // Get list of registered order statuses
        
        if ( function_exists( 'wc_get_order_statuses' ) ) {
            $wc_get_order_statuses = wc_get_order_statuses();
            
            if ( $wc_get_order_statuses ) {
                $status_list = array();
                foreach ( $wc_get_order_statuses as $key => $wc_status ) {
                    $status_list[$key] = $wc_status . ' (' . $key . ')';
                }
            }
        
        }
        
        $this->form_fields = array(
            'enabled' => array(
            'title'       => __( 'Enable/Disable', 'woocommerce-cross-seller' ),
            'type'        => 'checkbox',
            'desc_tip'    => true,
            'label'       => __( 'Enable this email notification', 'woocommerce-cross-seller' ),
            'description' => __( 'You can turn this feature on and off while you tweak the settings. If this is not turned on, no emails will be scheduled or sent.', 'woocommerce-cross-seller' ),
            'default'     => 'yes',
        ),
            'subject' => array(
            'title'       => __( 'Subject', 'woocommerce-cross-seller' ),
            'type'        => 'text',
            'description' => __( 'The email subject line.', 'woocommerce-cross-seller' ) . $macroliststring,
            'placeholder' => '',
            'default'     => __( '{customer_name}, have you seen these?', 'woocommerce-cross-seller' ),
        ),
        );
        $this->form_fields = array_merge( $this->form_fields, array(
            'emailheadline' => array(
            'title'       => __( 'Email heading', 'woocommerce-cross-seller' ),
            'type'        => 'text',
            'description' => __( 'The email heading.', 'woocommerce-cross-seller' ) . $macroliststring,
            'desc_tip'    => true,
            'placeholder' => '',
            'default'     => __( '{customer_name}, have you seen these?', 'woocommerce-cross-seller' ),
        ),
        ) );
        $this->form_fields = array_merge( $this->form_fields, array(
            'interval'        => array(
            'title'       => __( 'Day(s) after order', 'woocommerce-cross-seller' ),
            'type'        => 'text',
            'desc_tip'    => true,
            'description' => __( 'You can choose how many days after a order has been made before the emails are sent. You can add different days, seperated by comma. Default is <code>7,14,21,30</code>', 'woocommerce-cross-seller' ),
            'placeholder' => '7,14,21,30',
            'default'     => '7,14,21,30',
        ),
            'orderstatus'     => array(
            'title'       => __( 'Order status to schedule', 'woocommerce-cross-seller' ),
            'type'        => 'select',
            'desc_tip'    => true,
            'description' => __( 'When an order reaches a specific status, the reminder is scheduled to be sent. Choose which status. Default status is <code>Completed</code>.', 'woocommerce-cross-seller' ),
            'default'     => 'wc-completed',
            'options'     => $status_list,
        ),
            'excludeproducts' => array(
            'title'       => __( 'Exclude products', 'woocommerce-cross-seller' ),
            'type'        => 'text',
            'desc_tip'    => true,
            'description' => __( 'Ignore emails with these product ids. Comma-seperated list of product ids. No reminder emails with these product ids will be sent.', 'woocommerce-cross-seller' ),
            'default'     => '',
        ),
        ) );
        $this->form_fields = array_merge( $this->form_fields, array(
            'buttonbg'           => array(
            'title'       => __( 'Button Background Color', 'woocommerce-cross-seller' ),
            'type'        => 'text',
            'desc_tip'    => true,
            'css'         => 'width:6em;height:2em;',
            'description' => __( 'Background color for Shop Now buttons in email. Default <code>#ad74a2</code>.', 'woocommerce-cross-seller' ),
            'default'     => '#ad74a2',
            'class'       => 'colorpick',
        ),
            'buttoncolor'        => array(
            'title'       => __( 'Button Text Color', 'woocommerce-cross-seller' ),
            'type'        => 'text',
            'desc_tip'    => true,
            'css'         => 'width:6em;height:2em;',
            'description' => __( 'Font color for Shop Now buttons in email. Default <code>#ffffff</code>.', 'woocommerce-cross-seller' ),
            'default'     => '#ffffff',
            'class'       => 'colorpick',
        ),
            'buttontext'         => array(
            'title'       => __( 'Button Text', 'woocommerce-cross-seller' ),
            'type'        => 'text',
            'desc_tip'    => true,
            'description' => __( 'Button Text linking to products. Default is <code>Shop Now</code>.', 'woocommerce-cross-seller' ),
            'default'     => __( 'Shop Now', 'woocommerce-cross-seller' ),
            'placeholder' => __( 'Shop Now', 'woocommerce-cross-seller' ),
        ),
            'urlappend'          => array(
            'title'       => __( 'Append to link', 'woocommerce-cross-seller' ),
            'type'        => 'text',
            'desc_tip'    => true,
            'description' => __( 'Add tracking to links in emails, use for click tracking.', 'woocommerce-cross-seller' ),
            'placeholder' => '#utm_medium=Email&utm_campaign=WCCSell',
        ),
            'unsubscribesection' => array(
            'title'       => ' ',
            'type'        => 'title',
            'description' => '<h3>' . __( 'Email Unsubscribe Options', 'woocommerce-cross-seller' ) . '</h3><hr>',
        ),
            'stoptext'           => array(
            'title'       => __( 'Stop Receiving Emails Text', 'woocommerce-cross-seller' ),
            'type'        => 'text',
            'desc_tip'    => true,
            'description' => __( 'This text will be made in to a clickable link you can use with the <code>{unsubscribe_link}</code> macro.', 'woocommerce-cross-seller' ),
            'placeholder' => __( 'Unsubscribe from further emails', 'woocommerce-cross-seller' ),
            'default'     => __( 'Unsubscribe from further emails', 'woocommerce-cross-seller' ),
        ),
            'stoplink'           => array(
            'title'       => __( 'Unsubscribe page', 'woocommerce-cross-seller' ),
            'type'        => 'text',
            'desc_tip'    => true,
            'description' => __( 'Enter url for page to send customers who unsubscribes. This is the link that will be used in emails. If empty, the frontpage of your website will be used.', 'woocommerce-cross-seller' ),
            'placeholder' => site_url(),
            'default'     => '',
        ),
            'blocklist'          => array(
            'title'       => __( 'Email blocklist', 'woocommerce-cross-seller' ),
            'type'        => 'textarea',
            'desc_tip'    => true,
            'description' => __( 'Comma separated list of emails that will not receive any further emails.', 'woocommerce-cross-seller' ),
            'placeholder' => '',
            'default'     => '',
        ),
        ) );
        
        if ( defined( 'WC_LOG_DIR' ) ) {
            $log_url = add_query_arg( 'tab', 'logs', add_query_arg( 'page', 'wc-status', admin_url( 'admin.php' ) ) );
            $log_key = 'wc-cross-sell-' . sanitize_file_name( wp_hash( 'wc-cross-sell' ) ) . '-log';
            $log_url = add_query_arg( 'log_file', $log_key, $log_url );
            $label = ' | ' . sprintf( __( '%1$s View Log%2$s', 'woocommerce-cross-seller' ), '<a href="' . esc_url( $log_url ) . '">', '</a>' );
        }
        
        $this->form_fields = array_merge( $this->form_fields, array(
            'debuglog' => array(
            'title'       => __( 'Debug Log', 'woocommerce-cross-seller' ),
            'label'       => $label,
            'description' => __( 'To help you find problems and debug errors. Important events are always logged', 'woocommerce-cross-seller' ),
            'type'        => 'checkbox',
            'default'     => 'no',
        ),
        ) );
    }

}