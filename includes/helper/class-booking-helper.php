<?php

namespace WPT\Mobbex\Helper;

defined('ABSPATH') || exit;

final class Booking
{
    /**
     * Create a checkout from the booking given.
     * 
     * @param int $booking_id
     * @param bool $is_partial True if the payment will to be partial.
     * 
     * @return \Mobbex\Checkout
     */
    public function create_checkout($booking_id, $is_partial = false)
    {
        global $wt_cart;

        // Sanitize request, get traveller data and generate token for urls
        $request   = \WP_Travel::get_sanitize_request('request');
        $traveller = $this->get_traveller_data($request);
        $token     = md5(\Mobbex\Platform::$settings['api_key'] . '|' . \Mobbex\Platform::$settings['access_token']);

        // Format cart items
        foreach ($wt_cart->getItems() as $item) // TODO: Test this
            $items[] = [
                'total'       => $is_partial ? $item['trip_price_partial'] : $item['trip_price'],
                'quantity'    => 1,
                'description' => html_entity_decode(get_the_title($item['trip_id'])),
                'image'       => wptravel_get_post_thumbnail_url($item['trip_id']),
            ];

        // Format customer data
        $customer = [
            'name'           => $traveller['first_name'] . ' ' . $traveller['last_name'],
            'email'          => $traveller['email'],
            'identification' => $traveller['identification'],
            'phone'          => $traveller['phone'],
            'address'        => trim(preg_replace('/[0-9]/', '', (string) $traveller['address'])),
            'addressNumber'  => trim(preg_replace('/[^0-9]/', '', (string) $traveller['address'])),
            'zipCode'        => $traveller['postal'],
            'country'        => $this->convert_country_code($traveller['country']),
            'addressNotes'   => $traveller['note'],
            'userAgent'      => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null,
        ];

        // Create checkout and return
        return new \Mobbex\Checkout(
            $booking_id,
            $is_partial ? $wt_cart->get_total()['total_partial'] : $wt_cart->get_total()['total'],
            add_query_arg(compact('booking_id', 'token'), get_rest_url(null, 'wpt/mobbex/payment/callback')),
            add_query_arg(compact('booking_id', 'token'), get_rest_url(null, 'wpt/mobbex/payment/webhook')),
            $items,
            [],
            $customer
        );
    }

    /**
     * Return true if the booking need a payment and mobbex is selected.
     * 
     * @param int|string $booking_id
     * 
     * @return bool
     */
    public function need_payment($booking_id)
    {
        return (
            $booking_id
            && isset($_POST['wp_travel_payment_gateway'])
            && isset($_POST['wp_travel_booking_option'])
            && $_POST['wp_travel_payment_gateway'] == 'mobbex'
            && $_POST['wp_travel_booking_option']  == 'booking_with_payment'
        );
    }

    /**
     * Get first traveller data from a booking request.
     * 
     * @param array $request Santinized form data.
     * 
     * @return array 
     */
    public function get_traveller_data($request)
    {
        // First, get address from all travellers
        $first_names = isset($request['wp_travel_fname_traveller'])   ? $request['wp_travel_fname_traveller']   : [];
        $last_names  = isset($request['wp_travel_lname_traveller'])   ? $request['wp_travel_lname_traveller']   : [];
        $countries   = isset($request['wp_travel_country_traveller']) ? $request['wp_travel_country_traveller'] : [];
        $phones      = isset($request['wp_travel_phone_traveller'])   ? $request['wp_travel_phone_traveller']   : [];
        $emails      = isset($request['wp_travel_email_traveller'])   ? $request['wp_travel_email_traveller']   : [];
    
        // Obtain key of the first trip
        reset($first_names);
        $first_key = key($first_names);
    
        // Return data from the first traveller of first trip
        return [
            'first_name'     => isset($first_names[$first_key][0])   ? $first_names[$first_key][0]   : null,
            'last_name'      => isset($last_names[$first_key][0])    ? $last_names[$first_key][0]    : null,
            'country'        => isset($countries[$first_key][0])     ? $countries[$first_key][0]     : null,
            'phone'          => isset($phones[$first_key][0])        ? $phones[$first_key][0]        : null,
            'email'          => isset($emails[$first_key][0])        ? $emails[$first_key][0]        : null,
            'note'           => isset($request['wp_travel_note'])    ? $request['wp_travel_note']    : null,
            'postal'         => isset($request['billing_postal'])    ? $request['billing_postal']    : null,
            'identification' => isset($request['billing_dni'])       ? $request['billing_dni']       : null,
            'address'        => isset($request['wp_travel_address']) ? $request['wp_travel_address'] : null,
        ];
    }

    /**
     * Convert a booking country code to 3-letter ISO code.
     * 
     * @param string $code 2-Letter ISO code.
     * 
     * @return string|null
     */
    public function convert_country_code($code)
    {
        $countries = include('country-codes.php') ?: [];

        return isset($countries[$code]) ? $countries[$code] : null;
    }
}