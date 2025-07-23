<?php

if (!defined('ABSPATH')) exit;

class WIZEWPPH_Cron_Handler {

    const CRON_HOOK = 'wizewpph_daily_price_check';

    public static function init() {
        // Schedule cron job
        add_action('wp', [__CLASS__, 'maybe_schedule']);
        add_action(self::CRON_HOOK, [__CLASS__, 'run_daily_check']);
    }

    public static function maybe_schedule() {
        if (!wp_next_scheduled(self::CRON_HOOK)) {
            wp_schedule_event(time(), 'daily', self::CRON_HOOK);
        }
    }

    public static function run_daily_check() {
        // Get all published products
        $args = [
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
        ];

        $product_ids = get_posts($args);

        if (!empty($product_ids)) {
            foreach ($product_ids as $product_id) {
                self::track_product_price($product_id);
            }
        }
    }

    private static function track_product_price($product_id) {
        global $wpdb;

        $product = wc_get_product($product_id);
        if (!$product) return;

        $price = $product->get_regular_price();
        $sale_price = $product->get_sale_price();

        // Inserăm doar dacă avem preț valid (ignoram produse cu preț gol complet)
        if ($price === '') return;

        $table = $wpdb->prefix . WIZEWPPH_Database::TABLE_NAME;

        $wpdb->insert($table, [// phpcs:ignore WordPress.DB.DirectDatabaseQuery
            'product_id' => $product_id,
            'price' => $price,
            'sale_price' => $sale_price ?: null,
            'recorded_at' => current_time('mysql'),
        ]);
    }

    public static function unschedule() {
        $timestamp = wp_next_scheduled(self::CRON_HOOK);
        if ($timestamp) {
            wp_unschedule_event($timestamp, self::CRON_HOOK);
        }
    }
}