<?php

namespace Mobbex\WPT\Controllers;

defined('ABSPATH') || exit;

final class Payment
{
    /** @var \Mobbex\WPT\Helper\Booking */
    public $helper;

    /**
     * Register routes using wordpress hooks and rest api.
     * 
     * @return self
     */
    public function __construct()
    {
        $this->helper = new \Mobbex\WPT\Helper\Booking;

        add_action('wp_travel_after_frontend_booking_save', [$this, 'process']);
        add_action('wp_travel_before_partial_payment_complete', [$this, 'process'], 10, 2);

        add_action('rest_api_init', function () {
            register_rest_route('wpt/mobbex/payment', '/process', [
                'methods'             => 'POST',
                'callback'            => [$this, 'process'],
                'permission_callback' => '__return_true',
            ]);
            register_rest_route('wpt/mobbex/payment', '/callback', [
                'methods'             => 'GET',
                'callback'            => [$this, 'callback'],
                'permission_callback' => '__return_true',
            ]);
            register_rest_route('wpt/mobbex/payment', '/webhook', [
                'methods'             => 'POST',
                'callback'            => [$this, 'webhook'],
                'permission_callback' => '__return_true',
            ]);
        });
    }

    /**
     * Process a booking payment.
     * 
     * @param int|string $booking_id The booking to process.
     * @param bool $is_partial
     * 
     * @return bool|null Result of process. 
     */
    public function process($booking_id, $is_partial = false)
    {
        if (!$this->helper->need_payment($booking_id))
            return;

        if (!$is_partial)
            do_action('wt_before_payment_process', $booking_id);

        $checkout = $this->helper->create_checkout($booking_id, $is_partial);

        wp_redirect($checkout->url);
        exit;
    }

    /**
     * Handles the redirect after booking payment.
     */
    public function callback()
    {
        // Validate token and and status code
        $success = (
            !empty($_GET['status']) 
            && !empty($_GET['booking_id'])
            && !empty($_GET['token'])
            && \Mobbex\Repository::validateToken($_GET['token'])
            && $_GET['status'] > 1
            && $_GET['status'] < 400
        );

        // Redirects to thankyou page
        wp_redirect(
            add_query_arg(
                [
                    'booking_id' => isset($_GET['booking_id']) ? $_GET['booking_id'] : 0,
                    'booked'     => $success ? true : 'false',
                    '_nonce'     => isset($_GET['nonce']) ? $_GET['nonce'] : 0,
                ],
                wptravel_thankyou_page_url(
                    isset($_GET['booking_id']) ? get_post_meta($_GET['booking_id'], 'wp_travel_post_id', true) : null
                )
            )
        );
        exit;
    }

    /**
     * Handles the payment notification.
     */
    public function webhook()
    {
        $request = isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] == 'application/json' ? json_decode(file_get_contents('php://input'), true) : $_REQUEST;

        // Exit if webhook is not correctly formated
        if (empty($_REQUEST['booking_id']) || empty($_REQUEST['token']) || empty($request['data']))
            die('Error: Invalid webhook format.');

        // Exit if provided token does not match
        if (\Mobbex\Repository::validateToken($_REQUEST['token']))
            die('Error: Missmatch token.');

        $data = $request['data'];
        
        // Save transaction data
        update_post_meta($_REQUEST['booking_id'], 'mbbx_transaction', $data);

        // Get payment status
        $code = $data['payment']['status']['code'];

        if ($code == 2 || $code == 3 || $code == 100 || $code == 201) {
            $status = 'waiting';
        } else if ($code == 4 || $code >= 200 && $code < 400) {
            $status = 'paid';
        } else {
            $status = 'failed';
        }

        // Format data to show in booking widget
        $widget_data = [
            'ID del checkout'         => isset($data['checkout']['uid']) ? $data['checkout']['uid'] : 'N/A',
            'URL al cupon'            => 'https://mobbex.com/console/' . $data['entity']['uid'] . '/operations/?oid=' . $data['payment']['id'],
            'Moeda utilizada'         => isset($data['checkout']['currency']) ? $data['checkout']['currency'] : 'N/A',
            'ID de la transacción'    => isset($data['payment']['id']) ? $data['payment']['id'] : 'N/A',
            'Monto de la transacción' => isset($data['payment']['total']) ? wptravel_get_currency_symbol() . ' ' . $data['payment']['total'] : 'N/A',
            'Medio de pago'           => isset($data['payment']['source']['name']) ? $data['payment']['source']['name'] : 'N/A',
            'Número de tarjeta'       => isset($data['payment']['source']['number']) ? $data['payment']['source']['number'] : 'N/A',
            'Plan elegido'            => isset($data['payment']['source']['installment']['description']) ? $data['payment']['source']['installment']['description'] : 'N/A',
        ];

        // Update payment data
        wptravel_update_payment_status($_REQUEST['booking_id'], $data['payment']['total'], $status, $widget_data, '_mobbex_args');

        // Send emails and clear cart
        do_action( 'wp_travel_after_successful_payment', $_REQUEST['booking_id']);

        die('Webhook OK: ' . \Mobbex\Platform::to_string());
    }
}