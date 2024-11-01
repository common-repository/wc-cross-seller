<?php
/**
 * Customer Cross Sell Email Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p>Hello {customer_name},</p>

<p>Thank you for buying from us, we hope you enjoy your purchase :-)</p>

<p>We have a few other things you might want, take a look here.</p>

{products}

<p>Thank you,</p>

<p>{site_title}.</p>

<p>{unsubscribe_link}</p>

<?php
/**
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );