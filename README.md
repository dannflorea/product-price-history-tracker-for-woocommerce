# product-price-history-tracker-for-woocommerce
=== Product Price History Tracker for WooCommerce ===
Contributors: wizewp
Tags: woocommerce, price history, lowest price, omnibus directive, price tracker
Requires at least: 5.6
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Easily track WooCommerce product prices and display the lowest price in the last 30 days to comply with the EU Omnibus Directive.

== Description ==

**Product Price History Tracker for WooCommerce** helps you stay compliant with the EU Omnibus Directive by automatically recording product price changes and displaying the lowest price in the last 30 days on product pages.

The plugin works out of the box, is light-weight and fully integrates with WooCommerce.

**Key Features:**

- Automatically tracks price changes for all WooCommerce products
- Calculates and displays the lowest price in the last X days (default 30 days)
- Option to include or exclude sale prices in the calculation
- Optional display only for products currently on sale
- Fully customizable message using placeholders `{price}`, `{date}`, `{days}`
- Choose where to display the lowest price message on the product page (using WooCommerce hooks)
- Optional price history chart: inline display or open chart in popup
- Reset price history for individual products directly from product edit page
- Clean uninstall: all data can be safely removed when uninstalling

Compliant with EU regulations for price transparency and consumer protection.

== Screenshots ==

1. Lowest price message displayed on product page
2. Price history chart inline (example template)
3. Popup chart display
4. Admin settings page with configuration options
5. Reset price history per product in admin

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/product-price-history-tracker-for-woocommerce` directory, or install directly from WordPress plugin repository.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Configure plugin settings under `WizeWP > Woocommerce Price History`.

The plugin will start tracking prices automatically after activation.

== Frequently Asked Questions ==

= Will it work automatically after activation? =
Yes. The plugin will start recording prices for all products from the moment you activate it.

= Does it modify existing WooCommerce functionality? =
No. The plugin works independently, without altering WooCommerce core functionality.

= Can I reset the price history for a specific product? =
Yes. You can easily reset price history from the product edit page.

= Is it fully compliant with EU Omnibus Directive? =
Yes. This plugin implements the core functionalities required for price transparency under the EU Omnibus Directive.

= Is there a PRO version available? =
Yes. We are working on a PRO version that will include full historical tables, CSV export, advanced reporting, compliance audit logs, and more.

== Changelog ==

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.0.0 =
First stable release.

== External Services ==

This plugin connects to an external service provided by WizeWP (https://wizewp.com) in order to retrieve important product announcements, updates, offers or critical notifications.

- What data is sent: No personal data is transmitted. Only a simple HTTP GET request is performed to retrieve public JSON data.
- When: Only when you access the plugin's admin settings page.
- Service provided by: WizeWP (https://wizewp.com)
- Privacy Policy: https://wizewp.com/privacy-policy/
- Terms of Service: https://wizewp.com/terms-of-service/
