<?php

if (!defined('ABSPATH')) exit;

class WIZEWPPH_Admin_UI {

    public static function init() {
        add_action('admin_menu', [__CLASS__, 'add_plugin_subpage']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_assets']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_product_scripts']);
        add_action('add_meta_boxes', [__CLASS__, 'add_price_history_metabox']);
        add_action('wp_ajax_wizewpph_reset_product', [__CLASS__, 'handle_reset_product']);
        add_action('admin_notices', [__CLASS__, 'render_remote_notices']);
        add_action('wp_ajax_wizewpph_dismiss_notice', [__CLASS__, 'ajax_dismiss_notice']);
    }

    public static function add_plugin_subpage() {
        add_submenu_page(
            'wizemamo',
            __('Woocommerce Price History', 'product-price-history-tracker-for-woocommerce'),
            __('Woocommerce Price History', 'product-price-history-tracker-for-woocommerce'),
            'manage_options',
            'wize-wpph',
            [__CLASS__, 'render_plugin_page']
        );
    }
    public static function enqueue_assets($hook) {
        if ($hook !== 'toplevel_page_wizemamo' && $hook !== 'wizewp_page_wize-wpph') return;

        wp_enqueue_style(
            'wizewpph-admin-style',
            WIZEWPPH_ADMIN_URL . 'assets/css/admin.css',
            [],
            WIZEWPPH_VERSION
        );
        wp_enqueue_script(
            'wizewpph-admin-js',
            WIZEWPPH_ADMIN_URL . 'assets/js/admin.js',
            ['jquery'],
            WIZEWPPH_VERSION,
            true
        );
        wp_localize_script('wizewpph-admin-js', 'wizewpph_admin_data', [
            'nonce' => wp_create_nonce('wizewpph_dismiss_notice'),
        ]);
    }
    public static function enqueue_product_scripts($hook) {

        wp_enqueue_script('wizewpph-admin-reset', WIZEWPPH_ADMIN_URL . 'assets/js/admin-reset.js', [], WIZEWPPH_VERSION, true);
        wp_localize_script('wizewpph-admin-reset', 'wizewpph_reset_vars', [
            'nonce' => wp_create_nonce('wizewpph_reset_nonce')
        ]);
    }
    public static function render_plugin_page() {
        ?>
        <div class="wrap">
        <div class="wizemamo-admin-wrapper">
            <h1>WooCommerce Product Price History - EU Omnibus Compliance</h1>
            <p class="description">Easily track WooCommerce product prices and display the lowest price in the last 30 days to comply with the EU Omnibus Directive.</p>
            <p>Thank you for using our plugin! If you are satisfied, please reward it a full five-star <span class="stars">★★★★★</span> rating </p>
            <p><a href="https://wordpress.org/support/plugin/product-price-history-tracker-for-woocommerce/reviews/" target="_blank">Reviews</a> | <a href="https://wordpress.org/plugins/product-price-history-tracker-for-woocommerce/#developers" target="_blank">Changelog</a> | <a href="https://wordpress.org/support/plugin/product-price-history-tracker-for-woocommerce/" target="_blank">Discussion</a></p>
            <hr>
            <form method="post" action="options.php">
            <?php
                settings_fields('wizewpph_settings_group');
                do_settings_sections('wize-wpph');
                submit_button(__('Save settings', 'product-price-history-tracker-for-woocommerce'));
            ?>
            </form>
        </div>
        </div>
        <?php
    }
    
    public static function add_price_history_metabox() {
        add_meta_box(
            'wizewpph_reset_box',
            __('Price History', 'product-price-history-tracker-for-woocommerce'),
            [__CLASS__, 'render_reset_box'],
            'product',
            'side',
            'default'
        );
    }

    public static function render_reset_box($post) {
    ?>
    <p><?php esc_html_e('Reset recorded price history for this product.', 'product-price-history-tracker-for-woocommerce'); ?></p>
    <button type="button" class="button button-danger wizewpph-reset-button" data-product-id="<?php echo esc_attr($post->ID); ?>">
        <?php esc_html_e('Reset Price History', 'product-price-history-tracker-for-woocommerce'); ?>
    </button>
    <div class="wizewpph-reset-result"></div>
    <?php
}

    
    public static function handle_reset_product() {
    if (!current_user_can('manage_woocommerce')) {
        wp_send_json_error('Unauthorized');
    }

    if (!check_ajax_referer('wizewpph_reset_nonce', 'nonce', false)) {
        wp_send_json_error('Nonce verification failed');
    }

    $product_id = intval($_POST['product_id'] ?? 0);
    if ($product_id <= 0) {
        wp_send_json_error('Invalid product ID');
    }

    global $wpdb;
    $table = $wpdb->prefix . WIZEWPPH_Database::TABLE_NAME;
    $wpdb->delete($table, ['product_id' => $product_id]);// phpcs:ignore WordPress.DB.DirectDatabaseQuery

    wp_send_json_success();
}


    public static function render_remote_notices() {
        if (!current_user_can('manage_options')) return;
        $nonce = wp_create_nonce('wizewpph_dismiss_notice');
        $dismissed = get_user_meta(get_current_user_id(), 'wizewpph_dismissed_notices', true);
        if (!is_array($dismissed)) $dismissed = [];

        $response = wp_remote_get('https://wizewp.com/info/notices.json', ['timeout' => 5]);
        if (is_wp_error($response)) return;

        $notices = json_decode(wp_remote_retrieve_body($response), true);
        if (empty($notices) || !is_array($notices)) return;

        foreach ($notices as $notice) {
            if (empty($notice['id']) || in_array($notice['id'], $dismissed)) continue;
            ?>
            <div class="notice notice-info is-dismissible wizewpph-remote-notice" data-notice-id="<?php echo esc_attr($notice['id']); ?>">
                <p><strong><?php echo esc_html($notice['title']); ?></strong></p>
                <p><?php echo wp_kses_post($notice['message']); ?>
                    <?php if (!empty($notice['link_url']) && !empty($notice['link_text'])): ?>
                        <a href="<?php echo esc_url($notice['link_url']); ?>" target="_blank" class="button button-primary">
                            <?php echo esc_html($notice['link_text']); ?>
                        </a>
                    <?php endif; ?>
                </p>
            </div>
            <?php
        }
    }
    

    public static function ajax_dismiss_notice() {
        $nonce = isset($_POST['_ajax_nonce']) ? sanitize_text_field(wp_unslash($_POST['_ajax_nonce'])) : '';
        if (!wp_verify_nonce($nonce, 'wizewpph_dismiss_notice')) {
            wp_send_json_error(['message' => 'Nonce check failed']);
            wp_die();
        }

        $notice_id = isset($_POST['notice_id']) ? sanitize_text_field(wp_unslash($_POST['notice_id'])) : '';
        if (!$notice_id) {
            wp_send_json_error(['message' => 'Invalid notice ID']);
            wp_die();
        }

        $dismissed = get_user_meta(get_current_user_id(), 'wizewpph_dismissed_notices', true);
        if (!is_array($dismissed)) $dismissed = [];

        $dismissed[] = $notice_id;
        $dismissed = array_unique($dismissed);
        update_user_meta(get_current_user_id(), 'wizewpph_dismissed_notices', $dismissed);

        wp_send_json_success();
    }
}