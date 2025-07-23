<?php

if (!defined('ABSPATH')) exit;

class WIZEWPPH_Database {

    const TABLE_NAME = 'wizewpph_price_history';

    public static function init() {
        register_activation_hook(WIZEWPPH_PLUGIN_FILE, [__CLASS__, 'create_table']);
        register_deactivation_hook(WIZEWPPH_PLUGIN_FILE, [__CLASS__, 'remove_cron']);
    }

    public static function create_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME;

        // Check if table exists
        $table_exists = $wpdb->get_var(// phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $wpdb->prepare(
                "SELECT COUNT(*) FROM information_schema.TABLES 
                 WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s",
                DB_NAME,
                $table_name
            )
        );

        if ($table_exists) {
            // Table already exists, no need to create
            return;
        }

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            product_id BIGINT(20) UNSIGNED NOT NULL,
            variation_id BIGINT(20) UNSIGNED DEFAULT 0,
            price DECIMAL(20, 6) NOT NULL,
            sale_price DECIMAL(20, 6) DEFAULT NULL,
            recorded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY product_id (product_id),
            KEY variation_id (variation_id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    public static function remove_cron() {
        WIZEWPPH_Cron_Handler::unschedule();
    }
}