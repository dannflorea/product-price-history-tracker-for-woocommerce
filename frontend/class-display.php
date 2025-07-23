<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WIZEWPPH_Display {

    public static function init() {
        $options = get_option( 'wizewpph_settings' );
        $hook = $options['message_hook'] ?? 'woocommerce_single_product_summary';
        add_action( $hook, [ __CLASS__, 'display_lowest_price' ], 20 );
        add_shortcode( 'wizewpph_price_history', [ __CLASS__, 'shortcode_price_history' ] );
        add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_frontend_assets' ] );
        add_filter( 'woocommerce_product_tabs', [ __CLASS__, 'add_price_history_tab' ] );
    }

    public static function enqueue_frontend_assets() {
        wp_enqueue_style( 'wizewpph-frontend-style', WIZEWPPH_FRONTEND_URL . 'assets/css/style.css', [], WIZEWPPH_VERSION );
        wp_enqueue_script('chartjs', WIZEWPPH_FRONTEND_URL . 'assets/js/chart.umd.min.js', [], '4.5.0', true );
        wp_enqueue_script( 'wizewpph-chart', WIZEWPPH_FRONTEND_URL . 'assets/js/chart-display.js', [ 'chartjs' ], WIZEWPPH_VERSION, true );
    }

    public static function display_lowest_price() {
        $options = get_option( 'wizewpph_settings' );
        if ( empty( $options['enabled'] ) || empty( $options['show_lowest_price'] ) ) {
            return;
        }

        global $product;
        if ( ! $product instanceof WC_Product ) {
            return;
        }

        if ( ! empty( $options['only_promotions'] ) && ! $product->is_on_sale() ) {
            return;
        }

        $lowest_price_data = self::get_lowest_price( $product->get_id() );
        if ( ! $lowest_price_data ) {
            return;
        }

        $message_template = $options['lowest_price_message'] ?? __( 'Lowest price in last {days} days: {price}', 'product-price-history-tracker-for-woocommerce' );
        $days = absint( $options['keep_days'] ?? 30 );

        $placeholders = [
            '{price}' => wc_price( $lowest_price_data['price'] ),
            '{date}'  => date_i18n( get_option( 'date_format' ), strtotime( $lowest_price_data['recorded_at'] ) ),
            '{days}'  => $days,
        ];

        $final_message = strtr( $message_template, $placeholders );

        echo '<div class="wizewpph-lowest-price">';
        echo wp_kses_post( $final_message );
        echo '</div>';

        if ( ! empty( $options['show_chart'] ) ) {
            $mode = $options['chart_mode'] ?? 'inline';

            if ( $mode === 'inline' ) {
                self::render_chart( $product );
            } elseif ( $mode === 'popup' ) {
                self::render_popup_button( $product );
            }
        }
    }

    public static function get_lowest_price( $product_id ) {
        global $wpdb;

        $options = get_option( 'wizewpph_settings' );
        $days = absint( $options['keep_days'] ?? 30 );
        $include_sales = absint( $options['include_sales'] ?? 1 );
        $date_limit = gmdate( 'Y-m-d H:i:s', time() - ( $days * DAY_IN_SECONDS ) );

        $product_id = absint( $product_id );
        $table = $wpdb->prefix . 'wizewpph_price_history';

        // Caching
        $cache_key = 'lowest_price_' . $product_id . '_' . $include_sales . '_' . $days;
        $cached = wp_cache_get( $cache_key, 'wizewpph_price_history' );
        if ( false !== $cached ) {
            return $cached;
        }

        if ( $include_sales ) {
            $result = $wpdb->get_row(// phpcs:ignore WordPress.DB.DirectDatabaseQuery
                $wpdb->prepare(
                    "
                    SELECT 
                        CASE WHEN sale_price IS NOT NULL AND sale_price > 0 THEN sale_price ELSE price END AS lowest_price,
                        recorded_at
                    FROM {$wpdb->prefix}wizewpph_price_history
                    WHERE product_id = %d
                    AND recorded_at >= %s
                    ORDER BY lowest_price ASC
                    LIMIT 1
                    ",
                    $product_id,
                    $date_limit
                ),
                ARRAY_A
            );
        } else {
            $result = $wpdb->get_row(// phpcs:ignore WordPress.DB.DirectDatabaseQuery
                $wpdb->prepare(
                    "
                    SELECT price AS lowest_price, recorded_at
                    FROM {$wpdb->prefix}wizewpph_price_history
                    WHERE product_id = %d
                    AND recorded_at >= %s
                    ORDER BY price ASC
                    LIMIT 1
                    ",
                    $product_id,
                    $date_limit
                ),
                ARRAY_A
            );
        }

        if ( ! $result ) {
            return false;
        }

        $data = [
            'price'       => $result['lowest_price'],
            'recorded_at' => $result['recorded_at'],
        ];

        wp_cache_set( $cache_key, $data, 'wizewpph_price_history', HOUR_IN_SECONDS );
        return $data;
    }

    public static function render_chart( $product ) {
        $data = self::get_chart_data( $product );
        if ( empty( $data ) ) {
            return;
        }

        $labels_json = wp_json_encode( $data['labels'] );
        $labels_json_safe = htmlspecialchars( $labels_json, ENT_QUOTES, 'UTF-8' );
        $data_json = wp_json_encode( $data['prices'] );
        $data_json_safe = htmlspecialchars( $data_json, ENT_QUOTES, 'UTF-8' );

        $template = sanitize_text_field( get_option( 'wizewpph_settings' )['chart_template'] ?? 'basic' );

        echo '<div class="wizewpph-chart-container" style="max-width: 500px; margin-top: 20px;">
            <canvas id="wizewpphChart" data-labels=\'' . esc_attr( $labels_json_safe ) . '\' data-data=\'' . esc_attr( $data_json_safe ) . '\' data-template="' . esc_attr( $template ) . '"></canvas>
        </div>';
    }

    public static function render_popup_button( $product ) {
        $data = self::get_chart_data( $product );
        if ( empty( $data ) ) {
            return;
        }

        $product_id = absint( $product->get_id() );
        $labels_json = wp_json_encode( $data['labels'] );
        $labels_json_safe = htmlspecialchars( $labels_json, ENT_QUOTES, 'UTF-8' );
        $data_json = wp_json_encode( $data['prices'] );
        $data_json_safe = htmlspecialchars( $data_json, ENT_QUOTES, 'UTF-8' );

        $template = sanitize_text_field( get_option( 'wizewpph_settings' )['chart_template'] ?? 'basic' );

        echo '<div class="wizewpph-popup-wrapper" style="margin-top:15px;">';
        echo '<button type="button" class="button wizewpph-popup-trigger"
              data-product-id="' . esc_attr( $product_id ) . '"
              data-labels=\'' . esc_attr( $labels_json_safe ) . '\'
              data-data=\'' . esc_attr( $data_json_safe ) . '\'
              data-template="' . esc_attr( $template ) . '">'
              . esc_html__( 'View Price History', 'product-price-history-tracker-for-woocommerce' ) . '</button>';

        echo '<div class="wizewpph-modal" id="wizewpph-modal-' . esc_attr( $product_id ) . '">
                <div class="wizewpph-modal-content">
                    <span class="wizewpph-modal-close">&times;</span>
                    <div class="wizewpph-modal-chart-container"></div>
                </div>
              </div>';
        echo '</div>';
    }

    private static function get_chart_data( $product ) {
        global $wpdb;

        $product_id = absint( $product->get_id() );
        $variation_id = 0;
        if ( $product->is_type( 'variation' ) ) {
            $variation_id = $product_id;
            $product_id = absint( $product->get_parent_id() );
        }

        $days = absint( get_option( 'wizewpph_settings' )['keep_days'] ?? 30 );
        $date_limit = gmdate( 'Y-m-d H:i:s', time() - ( $days * DAY_IN_SECONDS ) );
        $table = $wpdb->prefix . 'wizewpph_price_history';

        // Cache chart data
        $cache_key = 'chart_' . $product_id . '_' . $variation_id . '_' . $days;
        $cached = wp_cache_get( $cache_key, 'wizewpph_price_history' );
        if ( false !== $cached ) {
            return $cached;
        }

        if ( $variation_id > 0 ) {
            $rows = $wpdb->get_results(// phpcs:ignore WordPress.DB.DirectDatabaseQuery
                $wpdb->prepare(
                    "
                    SELECT recorded_at, 
                        CASE WHEN sale_price IS NOT NULL AND sale_price > 0 THEN sale_price ELSE price END AS effective_price 
                    FROM {$wpdb->prefix}wizewpph_price_history
                    WHERE product_id = %d
                    AND variation_id = %d
                    AND recorded_at >= %s
                    ORDER BY recorded_at ASC
                    ",
                    $product_id,
                    $variation_id,
                    $date_limit
                )
            );
        } else {
            $rows = $wpdb->get_results(// phpcs:ignore WordPress.DB.DirectDatabaseQuery
                $wpdb->prepare(
                    "
                    SELECT recorded_at, 
                        CASE WHEN sale_price IS NOT NULL AND sale_price > 0 THEN sale_price ELSE price END AS effective_price 
                    FROM {$wpdb->prefix}wizewpph_price_history
                    WHERE product_id = %d
                    AND recorded_at >= %s
                    ORDER BY recorded_at ASC
                    ",
                    $product_id,
                    $date_limit
                )
            );
        }

        if ( empty( $rows ) ) {
            return [];
        }

        $labels = [];
        $prices = [];
        foreach ( $rows as $row ) {
            $labels[] = gmdate( 'M d', strtotime( $row->recorded_at ) );
            $prices[] = floatval( $row->effective_price );
        }

        $data = [
            'labels' => $labels,
            'prices' => $prices,
        ];

        wp_cache_set( $cache_key, $data, 'wizewpph_price_history', HOUR_IN_SECONDS );

        return $data;
    }

    public static function add_price_history_tab( $tabs ) {
        $options = get_option( 'wizewpph_settings' );
        if ( empty( $options['show_chart'] ) || ( $options['chart_mode'] ?? '' ) !== 'tab' ) {
            return $tabs;
        }

        $tabs['price_history'] = [
            'title'    => __( 'Price History', 'product-price-history-tracker-for-woocommerce' ),
            'priority' => 50,
            'callback' => [ __CLASS__, 'render_chart_tab_content' ]
        ];
        return $tabs;
    }

    public static function render_chart_tab_content() {
        global $product;
        self::render_chart( $product );
    }

    public static function shortcode_price_history( $atts ) {
        ob_start();
        self::display_lowest_price();
        return ob_get_clean();
    }
}
