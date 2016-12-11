<?php
namespace App;

class Economic
{
    protected $client;
    protected static $organizationId;
    protected static $accessToken;
    protected static $clientId;
    protected static $clientSecret;
    protected static $apiKey;


    protected function getClient()
    {
        if (!$this->client) {
            $this->client = new \GuzzleHttp\Client();

            $res = $this->client->request('GET', 'https://restapi.e-conomic.com/customers', [
                'verify' => false,
                'headers' => [
                    'X-AppSecretToken:' => 'demo',
                    'X-AgreementGrantToken' => 'demo',
                    'Content-Type' => 'application/json'
                ]
            ]);
            $response = self::convertJson($res);
            self::$accessToken = $response->access_token;
        }
        return $this->client;
    }

    public static function getContacts()
    {
        $res = self::getClient()->request('GET', 'https://restapi.e-conomic.com/customers ');

        return json_decode($res->getBody(), true);
    }
}
