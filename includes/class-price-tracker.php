<?php

if (!defined('ABSPATH')) exit;

class WIZEWPPH_Price_Tracker {

    public static function init() {
        add_action('save_post_product', [__CLASS__, 'record_price'], 20, 1);
        add_action('woocommerce_variation_set_stock', [__CLASS__, 'record_price_variation'], 20, 1);
        add_action('woocommerce_after_product_object_save', [__CLASS__, 'record_price_object'], 20, 1);
    }

    public static function record_price($post_id) {
        if (get_post_type($post_id) !== 'product') return;

        $product = wc_get_product($post_id);
        if (!$product) return;

        self::save_price_to_db($product);
    }

    public static function record_price_variation($variation) {
        if (!is_a($variation, 'WC_Product_Variation')) return;
        self::save_price_to_db($variation);
    }

    public static function record_price_object($product) {
        if (!is_a($product, 'WC_Product')) return;
        self::save_price_to_db($product);
    }

    private static function save_price_to_db($product) {
        global $wpdb;

        $product_id = absint( $product->get_id() );
        $variation_id = 0;

        if ($product->is_type('variation')) {
            $variation_id = $product_id;
            $product_id = absint( $product->get_parent_id() );
        }

        $price = wc_format_decimal( $product->get_regular_price() );
        $sale_price = wc_format_decimal( $product->get_sale_price() );

        $wpdb->insert(// phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $wpdb->prefix . WIZEWPPH_Database::TABLE_NAME,
            [
                'product_id'   => $product_id,
                'variation_id' => $variation_id,
                'price'        => $price,
                'sale_price'   => $sale_price,
                'recorded_at'  => current_time('mysql')
            ],
            [
                '%d',
                '%d',
                '%f',
                '%f',
                '%s'
            ]
        );
    }
}
