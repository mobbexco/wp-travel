<?php

namespace Mobbex;

defined('ABSPATH') || exit;

class Checkout
{
    /** Platform unique identifier for payment */
    public $reference;

    /** Module configured options */
    public $settings = [];

    /** Array with all mobbex response data */
    public $response = [];

    /** Mobbex unique identifier for payment */
    public $id;

    /** URL to go to pay */
    public $url;

    /** Available payment methods */
    public $methods;

    /** Cards saved from this customer */
    public $cards;

    /** Token to make payment through a transparent checkout */
    public $token;

    /**
     * Constructor.
     * 
     * @param int|string $id Identifier to generate reference and relate mobbex with platform.
     * @param int|string $total Amount to pay.
     * @param string $return_url Post-payment redirect URL.
     * @param string $webhook_url URL that recieve the Mobbex payment response.
     * @param array $installments
     * @param array $items {
     *     @type int|string $total Total amount to pay for this item.
     *     @type int $quantity Quantity of items. Does not modify the displayed total.
     *     @type string|null $description
     *     @type string|null $image Image URL to show in checkout.
     *     @type string|null $entity Entity configured to receive payment for this item.
     * }
     * @param array $customer {
     *     @type string $name
     *     @type string $email
     *     @type string $identification
     *     @type string|null $phone
     *     @type string|int|null $uid
     *     @type string|null $address Street name.
     *     @type string|null $addressNumber House number.
     *     @type string|null $zipCode Postal|ZIP code.
     *     @type string|null $state
     *     @type string|null $country Country ISO 3166-1 alpha-3 code.
     *     @type string|null $addressNotes
     *     @type string|null $userAgent
     * }
     * @param string $filter Name of hook to execute when body is filtered.
     */
    public function __construct(
        $id,
        $total,
        $return_url,
        $webhook_url,
        $items = [],
        $installments = [],
        $customer = [],
        $filter = 'wp_travel_mobbex_checkout'
    ) {
        $this->settings = \Mobbex\Platform::$settings;

        // Get merchants from items
        foreach ($items as $item) {
            if (isset($item['entity']))
                $merchants[] = ['uid' => $item['entity']];
        }

        // Make request and set response data as properties
        $this->set_response(\Mobbex\Api::request([
            'uri'    => 'checkout',
            'method' => 'POST',
            'body'   => apply_filters($filter, [
                'total'        => $total,
                'webhook'      => $webhook_url,
                'return_url'   => $return_url,
                'reference'    => $this->reference = $this->generate_reference($id),
                'description'  => 'Pedido #' . $id,
                'test'         => $this->settings['test_mode'] == 'yes',
                'multicard'    => $this->settings['multicard'] == 'yes',
                'multivendor'  => $this->settings['multivendor'],
                'wallet'       => $this->settings['wallet'] == 'yes' && wp_get_current_user()->ID,
                'intent'       => $this->settings['payment_mode'],
                'timeout'      => 5,
                'items'        => $items,
                'merchants'    => isset($merchants) ? $merchants : [],
                'installments' => $installments,
                'customer'     => $customer,
                'options'      => [
                    'embed'    => $this->settings['embed'] == 'yes',
                    'domain'   => str_replace('www.', '', parse_url(home_url(), PHP_URL_HOST)),
                    'theme'    => [
                        'type'       => $this->settings['theme'],
                        'background' => $this->settings['background'],
                        'header'     => [
                            'name' => $this->settings['title'] ?: get_bloginfo('name'),
                            'logo' => $this->settings['logo'],
                        ],
                        'colors'     => [
                            'primary' => $this->settings['color'],
                        ]
                    ],
                    'platform' => \Mobbex\Platform::to_array(),
                    'redirect' => [
                        'success' => true,
                        'failure' => false,
                    ],
                ],
            ], $id)
        ]));
    }

    /**
     * Set response data as class properties.
     * 
     * @param array $response
     */
    public function set_response($response)
    {
        $this->response = $response;
        $this->id       = isset($this->response['id'])              ? $this->response['id']              : null;
        $this->url      = isset($this->response['url'])             ? $this->response['url']             : null;
        $this->methods  = isset($this->response['paymentMethods'])  ? $this->response['paymentMethods']  : [];
        $this->cards    = isset($this->response['wallet'])          ? $this->response['wallet']          : [];
        $this->token    = isset($this->response['intent']['token']) ? $this->response['intent']['token'] : null;
    }

    /**
     * Generate a reference.
     * 
     * @param string|int $id Unique ID of the instance that will be related to the checkout.
     */
    public function generate_reference($id)
    {
        $reference = [
            \Mobbex\Platform::$name . '_id:' . $id,
            'time:' . time()
        ];

        // Add reseller id
        if (!empty($this->settings['reseller_id']))
            $reference[] = 'reseller:' . str_replace(' ', '-', trim($this->settings['reseller_id']));

        return implode('_', $reference);
    }
}