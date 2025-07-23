<?php
if (!defined('WP_UNINSTALL_PLUGIN')) exit;

global $wpdb;

$table = $wpdb->prefix . 'wizewpph_price_history';

$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wizewpph_price_history");// phpcs:ignore WordPress.DB.DirectDatabaseQuery

delete_option('wizewpph_settings');