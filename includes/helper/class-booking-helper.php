<?php

namespace Mobbex\WPT\Helper;

defined('ABSPATH') || exit;

final class Booking
{
    /**
     * Create a checkout from the booking given.
     * 
     * @param int $booking_id
     * @param bool $is_partial True if the payment will to be partial.
     * 
     * @return \Mobbex\Modules\Checkout
     */
    public function create_checkout($booking_id, $is_partial = false)
    {
        global $wt_cart;

        // Sanitize request and get traveller data
        $request   = \WP_Travel::get_sanitize_request('request');
        $traveller = $this->get_traveller_data($request);

        // Generate tokens for urls
        $token = md5(\Mobbex\Platform::$settings['api_key'] . '|' . \Mobbex\Platform::$settings['access_token']);
        $nonce = isset($request['_nonce']) ? $request['_nonce'] : null;

        // Format cart items
        foreach ($wt_cart->getItems() as $item)
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
            'phone'          => $traveller['phone']
        ];

        // Format adresses data
        $adresses = [
            'type'          => 'billing',
            'country'       => \Mobbex\Repository::convertCountryCode($traveller['country']),
            'state'         => '',
            'city'          => $traveller['city'],
            'zipCode'       => $traveller['postal'],
            'street'        => trim(preg_replace('/(\D{0})+(\d*)+$/', '', $traveller['address'])),
            'streetNumber'  => str_replace(preg_replace('/(\D{0})+(\d*)+$/', '', $traveller['address']), '', $traveller['address']),
            'streetNotes'   => $traveller['note'],
        ];

        // Create checkout and return
        return new \Mobbex\Modules\Checkout(
            $booking_id,
            $is_partial ? $wt_cart->get_total()['total_partial'] : $wt_cart->get_total()['total'],
            $this->get_endpoint_url('wpt/mobbex/payment/callback', compact('booking_id', 'token', 'nonce')),
            $this->get_endpoint_url('wpt/mobbex/payment/webhook', compact('booking_id', 'token', 'nonce')),
            $items,
            [],
            $customer,
            $adresses
        );
    }

    /**
     * Add Xdebug as query if debug mode is active
     * 
     * @param string $endpoint
     * @param array  $query
     * 
     * @return string new url query string (unescaped)
     * 
     */
    public function get_endpoint_url($endpoint, $query = [])
    {
        if (\Mobbex\Platform::$settings['debug_mode'])
            $query['XDEBUG_SESSION_START'] = 'PHPSTORM';

        return add_query_arg($query, get_rest_url(null, $endpoint));
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
     * Get lead traveller data from a booking request.
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
        $phones      = isset($request['wp_travel_phone_traveller'])   ? $request['wp_travel_phone_traveller']   : [];
        $emails      = isset($request['wp_travel_email_traveller'])   ? $request['wp_travel_email_traveller']   : [];
    
        // Obtain key of the first trip
        reset($first_names);
        $first_key = key($first_names);
    
        // Return data from the lead traveller of first trip
        return [
            'first_name'     => isset($first_names[$first_key][0])   ? $first_names[$first_key][0]   : null,
            'last_name'      => isset($last_names[$first_key][0])    ? $last_names[$first_key][0]    : null,
            'phone'          => isset($phones[$first_key][0])        ? $phones[$first_key][0]        : null,
            'email'          => isset($emails[$first_key][0])        ? $emails[$first_key][0]        : null,
            'country'        => isset($request['wp_travel_country']) ? $request['wp_travel_country'] : null,
            'note'           => isset($request['wp_travel_note'])    ? $request['wp_travel_note']    : null,
            'postal'         => isset($request['billing_postal'])    ? $request['billing_postal']    : null,
            'identification' => isset($request['billing_dni'])       ? $request['billing_dni']       : null,
            'address'        => isset($request['wp_travel_address']) ? $request['wp_travel_address'] : null,
            'city'           => isset($request['billing_city']) ? $request['billing_city'] : null,
        ];
    }
}