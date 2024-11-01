=== WooCommerce Cross-Seller ===
Contributors: cleverplugins, lkoudal, freemius
Donate link: https://cleverplugins.com
Tags: woocommerce, email, cross sell
Requires at least: 4.3
Requires PHP: 5.6
Tested up to: 5.1
Stable tag: 1.0.5

Keep customers engaged and get more sales with product recommendations. Automatically emails customers.

== Description ==

Email your customers recommendations with other products from your shop after purchase.

Keeps your customers engaged and your products fresh in mind. Bring in more sales with little effort.

Best used on webshops with more than a few products.

## FREE VERSION FEATURES
*  The plugin tries to show only unpurchased products. Every purchase made by a customer is checked so they are not suggested a product they have already purchased.

*  Unsubscribe functionality built-in. Allows customers to unsubscribe from getting any more product recommendation emails.

*  Change subject line and headline in settings. Pro version allows up to 5 of each.

*  Exclude products you don't want to promote.

Sends product suggestions to customers with other products from your webshop.

When you turn on the plugin is for all that are "completed" (you can change which status triggers this in settings) will be sent an email with other product suggestions from your webshop after 7, 14, 21 and 30 days.

You can change the interval in settings and also fine-tune the emails.

* The plugin comes with unsubscribe functionality so your customers can remove themselves from future product recommendations

* Works with WooCommerce logger system, see what is going on under the hood and debug errors faster.

## PRO VERSION FEATURES
* Cross sell on Thank You page. Show product recommendations on the thank you page.
* Customize the email content directly in the settings page. No need to edit files on your server, edit directly in editor with cool macros to customize the content.
* Set multiple subject lines. To randomize emails sent to your customers.


### Unsubscribe ###

Comes with builtin unsubscribe functionality, allowing your customers to unsubscribe from future emails. You can edit the comma separated list.

### Works with default WooCommerce emails ###

This plugin works with the default WooCommerce email templates and is developer friendly.

You can override the default template by copying the template files to your theme and change the text.

The pro version allows you to edit the content directly in the settings page.


== Installation ==

= Installing from WordPress =
1. Visit 'Plugins > Add New'
1. Search for 'WooCommerce Cross-Seller'
1. Activate WooCommerce Cross-Seller from your Plugins page.

= Installing Manually =
1. Download the plugin from wordpress.org
1. Unpack the .zip folder to your harddrive.
1. Upload the `wc-cross-seller` folder to the `/wp-content/plugins/` directory via FTP.
1. Activate theðWooCommerce Cross-Seller plugin through the 'Plugins' menu in WordPress

* Sends emails after x days to customer presenting new products.
* Integrates with WooCommerce - uses the built in template system.
* Easy to use and configure, set it up and let it work.
* Developer friendly - Customize the email template by copying to your child theme.
* Translatable - All strings can be translated with language files (pot file included) or a multilanguage plugin like WPML or Polylang.
* View scheduled emails - Which emails are ready to be sent.
* Log - See what emails have been sent and extra information to help with debugging.


== Frequently Asked Questions ==

None so far.

== Screenshots ==
1. Example email #1
2. Example email #2
3. The settings page - many options to tweak the plugin as you wish
4. Unsubscribe settings

== Changelog ==

= 1.0.5 = 
* Security update to vendor library, please update.

= 1.0.4 = 
* Security fix, please update.

= 1.0.2 =
* New macro - {product_names} which outputs the suggested product names in a string format separated by a comma. "Product 1, product 2, ".. etc. Use in subject line or headline or put in the content text.
* Fixed missing translations here and there.
* Updated language .pot file.
* Removed test e-mail functionality temporarily, I need to do some testing.
* Changed name to WooCommerce Cross-Seller.

= 1.0.1 =
* First public release

== Upgrade Notice ==

= 1.0.5 =
* Security update to vendor library, please update.