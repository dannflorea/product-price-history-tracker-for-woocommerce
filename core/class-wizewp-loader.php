<?php

if (!defined('ABSPATH')) exit;

class WIZEWPPH_Loader {

    public static function init() {
        add_action('admin_menu', [__CLASS__, 'maybe_create_main_menu'], 5);
    }

    public static function maybe_create_main_menu() {
        global $menu;

        $menu_exists = false;

        foreach ($menu as $item) {
            if (isset($item[2]) && $item[2] === 'wizemamo') {
                $menu_exists = true;
                break;
            }
        }
        $icon_url = plugins_url('admin/assets/img/wpwize-icon-20.png', WIZEWPPH_ADMIN_PATH);
        if (!$menu_exists) {
            add_menu_page(
                __('WizeWP', 'product-price-history-tracker-for-woocommerce'),
                __('WizeWP', 'product-price-history-tracker-for-woocommerce'),
                'manage_options',
                'wizemamo',
                [__CLASS__, 'render_main_page'],
                $icon_url,
                28
            );
        }
    }

    public static function render_main_page() {
        ?>
        <div class="wrap">
            <div class="wizemamo-admin-wrapper">
                <h1>WizeWP | Wize Up Your Website</h1>
                <p>We innovate and create quality products that will help you get to your goals faster and easier so that you will have time to focus on what's important.</p>
            </div>
            <?php
            $plugins = self::get_plugins_from_wp_org();
            ?>
            <?php if($plugins) :?>
            <div class="wizemamo-admin-wrapper" style="margin-top: 20px;">
            <h2><?php esc_html_e('Our Plugins', 'product-price-history-tracker-for-woocommerce'); ?></h2>
            <div id="wizemamo-plugins-list">
                <?php
                echo '<div class="wizemamo-plugins-grid">';
                foreach ($plugins as $plugin) {
                    echo '<div class="wizemamo-plugin-card">';
                    echo '<h2><a href="https://wordpress.org/plugins/' . esc_html($plugin['slug']) . '" target="_blank">' . esc_html($plugin['name']) . '</a></h2>';
                    echo '<p>' . esc_html(wp_trim_words($plugin['short_description'], 15)) . '</p>';
                    echo '<a class="button button-primary" href="https://wordpress.org/plugins/' . esc_html($plugin['slug']) . '" target="_blank">See on WordPress.org</a>';
                    echo '</div>';
                }
                echo '<div>';
                ?>
            </div>
            <?php endif;?>
            </div>
            <div class="wizemamo-admin-wrapper" style="margin-top: 20px;">
            <div class="wizemamo-contact-section">
            <h2>📬 Contact & Support</h2>
            <p>If you have any questions, suggestions or encounter any problems, write to us with confidence:</p>
            <ul>
                <li><strong>Email:</strong> <a href="mailto:support@wizewp.com">support@wizewp.com</a></li>
                <li><strong>Website:</strong> <a href="https://wizewp.com" target="_blank">www.wizewp.com</a></li>
                <li><strong>WordPress.org:</strong> <a href="https://profiles.wordpress.org/wizewp/" target="_blank">wizeWP WordPress.org</a></li>
            </ul>
            </div>
            </div>
        </div>
        <?php
    }
    public static function get_plugins_from_wp_org() {
        if(!is_admin()){
            return false;
        }
    $transient_key = 'wizemamo_mm_plugins_cache';
    $cached = get_transient($transient_key);
    if ($cached !== false) {
        return $cached;
    }

    if (!function_exists('plugins_api')) {
        return false;
    }

    $args = [
        'author'   => 'wizewp',
        'page'     => 1,
        'per_page' => 10,
        'fields'   => [
            'short_description' => true,
            'icons' => true,
            'active_installs' => true,
            'slug' => true,
            'name' => true,
            'homepage' => true
        ],
    ];

    $response = plugins_api('query_plugins', $args);
    if (is_wp_error($response) || empty($response->plugins)) {
        return false;
    }

    $plugins = array_map(function ($plugin) {
        return [
            'name'              => $plugin['name'] ?? '',
            'short_description'=> $plugin['short_description'] ?? '',
            'homepage'          => $plugin['homepage'] ?? '',
            'slug'              => $plugin['slug'] ?? '',
            'icon'              => $plugin['icons']['1x'] ?? '',
        ];
    }, $response->plugins);

    set_transient($transient_key, $plugins, HOUR_IN_SECONDS * 6);

    return $plugins;
    }
}