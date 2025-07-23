<?php
/*
 Plugin Name: Product Price History Tracker for WooCommerce
 Plugin URI: https://wizewp.com/plugins/woocommerce-price-history/
 Description: Start tracking price history automatically and display clear lowest price information to fully comply with EU Omnibus Directive.
 Version: 1.0.0
 Author: WizeWP
 Author URI:  https://www.wizewp.com
 License:     GPL2
 License URI: https://www.gnu.org/licenses/gpl-2.0.html
 Text Domain: product-price-history-tracker-for-woocommerce
 Requires Plugins:  woocommerce
 Domain Path: /languages/
 */

if (!defined('ABSPATH')) exit;

// Define constants
define('WIZEWPPH_VERSION', '1.0.0');
define('WIZEWPPH_PATH', plugin_dir_path(__FILE__));
define('WIZEWPPH_URL', plugin_dir_url(__FILE__));
define('WIZEWPPH_PLUGIN_FILE', __FILE__);

define('WIZEWPPH_ADMIN_PATH', WIZEWPPH_PATH . 'admin/');
define('WIZEWPPH_CORE_PATH', WIZEWPPH_PATH . 'core/');
define('WIZEWPPH_INCLUDES_PATH', WIZEWPPH_PATH . 'includes/');
define('WIZEWPPH_FRONTEND_PATH', WIZEWPPH_PATH . 'frontend/');

define('WIZEWPPH_ADMIN_URL', WIZEWPPH_URL . 'admin/');
define('WIZEWPPH_FRONTEND_URL', WIZEWPPH_URL . 'frontend/');

// Load core functionality
require_once WIZEWPPH_CORE_PATH . 'class-wizewp-loader.php';

// Initialize WizeWP loader (create main menu if needed)
WIZEWPPH_Loader::init();

// Load includes (core logic)
require_once WIZEWPPH_INCLUDES_PATH . 'class-database.php';
require_once WIZEWPPH_INCLUDES_PATH . 'class-price-tracker.php';
require_once WIZEWPPH_INCLUDES_PATH . 'class-cron-handler.php';

WIZEWPPH_Database::init();
WIZEWPPH_Price_Tracker::init();
WIZEWPPH_Cron_Handler::init();

require_once WIZEWPPH_ADMIN_PATH . 'class-admin-ui.php';
require_once WIZEWPPH_ADMIN_PATH . 'class-admin-settings.php';
WIZEWPPH_Admin_UI::init();
WIZEWPPH_Admin_Settings::init();

// Load frontend logic
require_once WIZEWPPH_FRONTEND_PATH . 'class-display.php';
WIZEWPPH_Display::init();