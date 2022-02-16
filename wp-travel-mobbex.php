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
 * Save configuration values.
 *
 * @param array $settings WP Travel config values.
 * @param array $request Requested values.
 * 
 * @return array
 */
add_filter('wp_travel_block_before_save_settings', function ($settings, $request) {
    return array_merge($settings, [
        'payment_option_mobbex' => !empty($request['payment_option_mobbex']) ? 'yes'                           : null,
        'mobbex_test_mode'      => !empty($request['mobbex_test_mode'])      ? 'yes'                           : null,
        'mobbex_api_key'        => !empty($request['mobbex_api_key'])        ? $request['mobbex_api_key']      : null,
        'mobbex_access_token'   => !empty($request['mobbex_access_token'])   ? $request['mobbex_access_token'] : null,
    ]);
}, 10, 2);

/**
 * Enqueue admin module scripts.
 */
add_action('admin_enqueue_scripts', function () {
    if (WP_Travel::is_page('settings', true))
        wp_enqueue_script('mbbx-settings-js', WPT_MOBBEX_URL . 'assets/js/settings.js', null, WPT_MOBBEX_VERSION);
});

/**
 * Add customer identification field to checkout billing form.
 * 
 * @param array $fields Current billing fields.
 * 
 * @return array
 */
add_action('wp_travel_checkout_billing_fields', function ($fields) {
    return array_merge($fields, [
        'billing_dni' => [
            'type'        => 'text',
            'label'       => __('DNI', 'wp-travel-mobbex'),
            'name'        => 'billing_dni',
            'id'          => 'billing_dni',
            'priority'    => 10,
            'validations' => [
                'required'  => true,
                'maxlength' => '30',
            ],
        ],
    ]);
});