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

// Define constants
define('WPT_MOBBEX_VERSION', '1.0.0');
define('WPT_MOBBEX_PATH', plugin_dir_path(__FILE__));
define('WPT_MOBBEX_URL', plugin_dir_url(__FILE__));

// Include base classes
require_once WPT_MOBBEX_PATH . 'includes/lib/class-api.php';
require_once WPT_MOBBEX_PATH . 'includes/lib/class-platform.php';
require_once WPT_MOBBEX_PATH . 'includes/lib/class-exception.php';

// Load module classes
require_once WPT_MOBBEX_PATH . 'includes/lib/modules/class-checkout.php';

// Include helpers
require_once WPT_MOBBEX_PATH . 'includes/helper/class-booking-helper.php';

// Include controllers
require_once WPT_MOBBEX_PATH . 'controllers/payment.php';

// Include external module classes
require_once WPT_MOBBEX_PATH . 'updater/plugin-update-checker.php';

/**
 * Init plugin.
 */
add_action('plugins_loaded', function () {
    $settings = [];

    // Get current module settings
    foreach (wptravel_get_settings() as $key => $value)
        if (strpos($key, 'mobbex_') === 0)
            $settings[str_replace('mobbex_', '', $key)] = $value;

    // Set platform information
    \Mobbex\Platform::init('wp_travel', WPT_MOBBEX_VERSION, [
        'wordpress' => get_bloginfo('version'),
        'wp_travel' => WP_TRAVEL_VERSION,
    ], $settings);

    // Init api conector
    \Mobbex\Api::init();

    // Init controllers
    new \WPT\Mobbex\Controllers\Payment;

    // Init update checker
    $updater = \Puc_v4_Factory::buildUpdateChecker('https://github.com/mobbexco/wp-travel/', __FILE__, 'wp-travel-mobbex');
    $updater->getVcsApi()->enableReleaseAssets();
});

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

/**
 * Add own payment status to wp-travel list.
 * 
 * @param array $status All current wp-travel status.
 * 
 * @return array
 */
add_filter('wp_travel_payment_status_list', function ($status) {
    return array_merge($status, [
        'waiting' => [
            'color' => '#e5cd00',
            'text'  => __('En espera', 'wp-travel-mobbex'),
        ],
        'failed'  => [
            'color' => '#dd332f',
            'text'  => __('Pago fallido', 'wp-travel-mobbex'),
        ],
        'suspected_fraud' => [
            'color' => '#ff0600',
            'text'  => __('Sospecha de fraude', 'wp-travel-mobbex'),
        ],
    ]);
});