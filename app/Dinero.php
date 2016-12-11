<?php
namespace App;

class Dinero
{
    public static $client;
    public static $instance;
    protected static $organizationId;
    protected static $accessToken;
    protected static $clientId;
    protected static $clientSecret;
    protected static $apiKey;

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function getClient()
    {
        if (!self::$client) {
            self::$client = new \GuzzleHttp\Client();

            $token = base64_encode(self::$clientId . ':' . self::$clientSecret);
            $res = self::$client->request('POST', 'https://authz.dinero.dk/dineroapi/oauth/token', [
                'verify' => false,
                'headers' => [
                    'Authorization' => 'Basic ' . $token
                ],
                'form_params' => [
                    'grant_type' => 'password',
                    'scope' => 'read write',
                    'username' => self::$apiKey,
                    'password' => self::$apiKey
                ]
            ]);
            $response = self::convertJson($res);
            self::$accessToken = $response->access_token;
        }
        return self::$client;
    }

    protected static function convertJson($response)
    {
        $body = $response->getBody();
        $json = '';
        while (!$body->eof()) {
            $json .= $body->read(1024);
        }
        return json_decode($json);
    }

    public static function initialize($dbRow)
    {
        self::$organizationId = $dbRow['org_id'];
        self::$clientId = config('services.dinero.client');
        self::$clientSecret = config('services.dinero.secret');
        self::$apiKey = $dbRow['api_key'];
    }

    public static function createInvoice($params)
    {
        $res = self::getClient()->request('POST', 'https://api.dinero.dk/v1/' . self::$organizationId . '/invoices', [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Bearer ' . self::$accessToken
            ],
            'json' => $params
        ]);

        return self::convertJson($res);
    }

    public static function bookInvoice($invoiceGuid, $timestamp)
    {
        $res = self::getClient()->request('POST', 'https://api.dinero.dk/v1/'
            . self::$organizationId . '/invoices/' . $invoiceGuid . '/book', [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Bearer ' . self::$accessToken
            ],
            'json' => [
                'timestamp' => $timestamp]
        ]);
        return self::convertJson($res);
    }

    public static function sendInvoice($invoiceGuid, $timestamp)
    {
        $res = self::getClient()->request('POST', 'https://api.dinero.dk/v1/'
            . self::$organizationId . '/invoices/' . $invoiceGuid . '/email', [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Bearer ' . self::$accessToken
            ],
            'json' => [
                'timestamp' => $timestamp]
        ]);
        return $res;
    }

    public function getContacts()
    {
        $res = self::getClient()->request('GET', 'https://api.dinero.dk/v1/'
            . self::$organizationId . '/contacts?field=Name,ContactGuid', [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Bearer ' . self::$accessToken
            ]
        ]);

        $request = json_decode($res->getBody(), true);

        $results = [];
        $i = 0;
        foreach ($request['Collection'] as $contact) {
            $results[$i]['name'] = $contact['name'];
            $results[$i]['guid'] = $contact['contactGuid'];
            $i++;
        }

        return $results;
    }
}
