<?php

namespace Mobbex;

defined('ABSPATH') || exit;

class Api
{
    public $ready = false;

    /** Mobbex API base URL */
    public $api_url = 'https://api.mobbex.com/p/';

    /** Commerce API Key */
    private $api_key;

    /** Commerce Access Token */
    private $access_token;

    /**
     * Set Mobbex credentails.
     * 
     * @param string $api_key Commerce API Key.
     * @param string $access_token Commerce Access Token.
     */
    public static function init($api_key, $access_token)
    {
        self::$api_key      = $api_key      ?: \Mobbex\Platform::$settings['api_key'];
        self::$access_token = $access_token ?: \Mobbex\Platform::$settings['access_token'];
        self::$ready        = $api_key && $access_token;
    }

    /**
     * Make a request to Mobbex API.
     * 
     * @param array $data 
     * 
     * @return mixed Result status or data if exists.
     * 
     * @throws \Mobbex\Exception
     */
    public static function request($data)
    {
        if (!self::$ready)
            return false;

        if (empty($data['method']) || empty($data['uri']))
            throw new \Mobbex\Exception('Mobbex request error: Missing arguments', 0, $data);

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL            => self::$api_url . $data['uri'] . (!empty($data['params']) ? '?' . http_build_query($data['params']) : null),
            CURLOPT_HTTPHEADER     => self::get_headers(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => $data['method'],
            CURLOPT_POSTFIELDS     => !empty($data['body']) ? json_encode($data['body']) : null,
        ]);

        $response = curl_exec($curl);
        $error    = curl_error($curl);

        curl_close($curl);

        // Throw curl errors
        if ($error)
            throw new \Mobbex\Exception('Curl error in Mobbex request #:' . $error, curl_errno($curl), $data);

        $result = json_decode($response, true);

        // Throw request errors
        if (!$result)
            throw new \Mobbex\Exception('Mobbex request error: Invalid response format', 0, $data);

        if (!$result['result'])
            throw new \Mobbex\Exception('Mobbex request error #' . $result['code'] . ': ' . $result['error'], 0, $data);

        return isset($result['data']) ? $result['data'] : $result['result'];
    }

    /**
     * Get headers to connect with Mobbex API.
     * 
     * @return string[] 
     */
    private static function get_headers()
    {
        return [
            'cache-control: no-cache',
            'content-type: application/json',
            'x-api-key: ' . self::$api_key,
            'x-access-token: ' . self::$access_token,
            'x-ecommerce-agent: ' . \Mobbex\Platform::to_string(),
        ];
    }
}