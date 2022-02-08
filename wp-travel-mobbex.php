<?php
/**
 * Plugin Name: Mobbex for WP-Travel
 * Plugin URI: https://github.com/mobbexco/wp-travel
 * Description: The best choice for a Travel Agency, Tour Operator or Destination Management Company, wanting to manage packages more efficiently & increase sales.
 * Version: 1.0.0
 * Author: Mobbex
 * Author URI: https://mobbex.com/
 * Requires PHP: 5.6
 * WP-Travel tested up to: 5.0.7
 *
 * Text Domain: wp-travel-mobbex
 * Domain Path: /languages/
 */

defined('ABSPATH') || exit;

/**
 * Add gateway to wp-travel list.
 *
 * @param array $gateways WP Travel added gateways.
 * 
 * @return array
 */
add_filter('wp_travel_payment_gateway_lists', function ($gateways) {
    return array_merge([
        'mobbex' => 'Mobbex',
    ], $gateways);
});


/**
 * Set module options default values.
 *
 * @param array $settings WP Travel config values.
 * 
 * @return array
 */
add_filter('wp_travel_settings_values', function ($settings) {
    return array_merge([
        'payment_option_mobbex' => null,
        'mobbex_test_mode'      => null,
        'mobbex_api_key'        => null,
        'mobbex_access_token'   => null,
    ], $settings);
});

/**
 * Enqueue admin module scripts.
 */
add_action('admin_enqueue_scripts', function () {
    if (WP_Travel::is_page('settings', true))
        wp_enqueue_script('mbbx-hooks-js', plugin_dir_url(__FILE__) . 'assets/js/settings.js');
});