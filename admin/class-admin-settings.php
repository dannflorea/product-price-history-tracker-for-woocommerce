<?php

if (!defined('ABSPATH')) exit;

class WIZEWPPH_Admin_Settings {

    public static function init() {
        add_action('admin_init', [__CLASS__, 'register_settings']);
    }

    public static function register_settings() {
        register_setting('wizewpph_settings_group', 'wizewpph_settings', [__CLASS__, 'sanitize_settings']);

        add_settings_section(
            'wizewpph_main_section',
            __('Price History Settings', 'product-price-history-tracker-for-woocommerce'),
            '__return_false',
            'wize-wpph'
        );

        self::add_field('enabled', 'Enable Price History Tracking', 'field_enabled');
        self::add_field('keep_days', 'Days to Keep Price History', 'field_keep_days');
        self::add_field('show_lowest_price', 'Display Lowest Price Text', 'field_show_lowest_price');
        self::add_field('include_sales', 'Include sale prices in calculation', 'field_include_sales');
        self::add_field('only_promotions', 'Display only for promotional products', 'field_only_promotions');
        self::add_field('lowest_price_message', 'Lowest price message template', 'field_lowest_price_message');
        self::add_field('message_hook', 'Message placement (WooCommerce hook)', 'field_message_hook');
        self::add_field('show_chart', 'Display Chart on Product Page', 'field_show_chart');
        self::add_field('chart_mode', 'Chart Display Mode', 'field_chart_mode');
        self::add_field('chart_template', 'Chart Template', 'field_chart_template');
    }

    public static function sanitize_settings($input) {
        $sanitized = [];

        $sanitized['enabled'] = isset($input['enabled']) ? 1 : 0;
        $sanitized['keep_days'] = intval($input['keep_days']);
        $sanitized['show_lowest_price'] = isset($input['show_lowest_price']) ? 1 : 0;
        $sanitized['keep_days'] = absint($input['keep_days'] ?? 30);
        $sanitized['include_sales'] = isset($input['include_sales']) ? 1 : 0;
        $sanitized['only_promotions'] = isset($input['only_promotions']) ? 1 : 0;
        $sanitized['lowest_price_message'] = sanitize_text_field($input['lowest_price_message'] ?? '');
        $sanitized['message_hook'] = sanitize_text_field($input['message_hook'] ?? 'woocommerce_single_product_summary');
        $sanitized['show_chart'] = isset($input['show_chart']) ? 1 : 0;
        $sanitized['chart_mode'] = sanitize_text_field($input['chart_mode']);
        $sanitized['chart_template'] = sanitize_text_field($input['chart_template']);

        return $sanitized;
    }

    private static function add_field($id, $label, $callback) {
        add_settings_field(
            $id,
            $label,
            [__CLASS__, $callback],
            'wize-wpph',
            'wizewpph_main_section'
        );
    }

    public static function field_enabled() {
        $options = get_option('wizewpph_settings');
        ?>
        <label>
            <input type="checkbox" name="wizewpph_settings[enabled]" value="1" <?php checked(1, $options['enabled'] ?? 0); ?> />
            <?php esc_html_e('Activate price tracking', 'product-price-history-tracker-for-woocommerce'); ?>
        </label>
        <?php
    }

    public static function field_keep_days() {
        $options = get_option('wizewpph_settings');
        $selected = $options['keep_days'] ?? 30;
        ?>
        <select name="wizewpph_settings[keep_days]">
            <?php foreach ([30, 60, 90] as $days): ?>
                <option value="<?php echo esc_attr($days); ?>" <?php selected($selected, $days); ?>>
                    <?php echo esc_html($days . ' days'); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    public static function field_show_lowest_price() {
        $options = get_option('wizewpph_settings');
        ?>
        <label>
            <input type="checkbox" name="wizewpph_settings[show_lowest_price]" value="1" <?php checked(1, $options['show_lowest_price'] ?? 0); ?> />
            <?php esc_html_e('Show lowest price text on product page', 'product-price-history-tracker-for-woocommerce'); ?>
        </label>
        <?php
    }
    
    public static function field_include_sales() {
        $options = get_option('wizewpph_settings');
        ?>
        <label>
            <input type="checkbox" name="wizewpph_settings[include_sales]" value="1" <?php checked(1, $options['include_sales'] ?? 1); ?> />
                <?php esc_html_e('Include sale prices when calculating lowest price', 'product-price-history-tracker-for-woocommerce'); ?>
        </label>
        <?php
    }
    
    public static function field_only_promotions() {
        $options = get_option('wizewpph_settings');
        ?>
        <label>
            <input type="checkbox" name="wizewpph_settings[only_promotions]" value="1" <?php checked(1, $options['only_promotions'] ?? 0); ?> />
            <?php esc_html_e('Show message only if product is on sale', 'product-price-history-tracker-for-woocommerce'); ?>
        </label>
        <?php
    }
    
    public static function field_lowest_price_message() {
        $options = get_option('wizewpph_settings');
        ?>
        <input type="text" name="wizewpph_settings[lowest_price_message]" value="<?php echo esc_attr($options['lowest_price_message'] ?? 'The lowest price in last {days} days: {price}'); ?>" class="regular-text" />
        <p class="description">{price}, {date}, {days} can be used as placeholders.</p>
        <?php
    }
    
    public static function field_message_hook() {
        $options = get_option('wizewpph_settings');
        $selected = $options['message_hook'] ?? 'woocommerce_single_product_summary';

        $hooks = [
            'woocommerce_single_product_summary' => 'After Price',
            'woocommerce_product_meta_start' => 'Product meta start',
            'woocommerce_after_single_product_summary' => 'After product summary',
        ];

        ?>
        <select name="wizewpph_settings[message_hook]">
            <?php foreach ($hooks as $hook => $label): ?>
                <option value="<?php echo esc_attr($hook); ?>" <?php selected($selected, $hook); ?>><?php echo esc_html($label); ?></option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    public static function field_show_chart() {
        $options = get_option('wizewpph_settings');
        ?>
        <label>
            <input type="checkbox" name="wizewpph_settings[show_chart]" value="1" <?php checked(1, $options['show_chart'] ?? 0); ?> />
            <?php esc_html_e('Display price history chart', 'product-price-history-tracker-for-woocommerce'); ?>
        </label>
        <?php
    }

    public static function field_chart_mode() {
        $options = get_option('wizewpph_settings');
        $selected = $options['chart_mode'] ?? 'inline';
        ?>
        <select name="wizewpph_settings[chart_mode]">
            <option value="inline" <?php selected($selected, 'inline'); ?>>Inline</option>
            <option value="tab" <?php selected($selected, 'tab'); ?>>Tab</option>
            <option value="popup" <?php selected($selected, 'popup'); ?>>Popup</option>
        </select>
        <?php
    }

    public static function field_chart_template() {
        $options = get_option('wizewpph_settings');
        $selected = $options['chart_template'] ?? 'basic';
        ?>
        <select name="wizewpph_settings[chart_template]">
            <option value="basic" <?php selected($selected, 'basic'); ?>>Basic Line Chart</option>
            <option value="smooth" <?php selected($selected, 'smooth'); ?>>Smooth Gradient Line</option>
            <option value="bar" <?php selected($selected, 'bar'); ?>>Bar Chart</option>
        </select>
        <?php
    }
}
