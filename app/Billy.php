<?php
namespace App;

use Carbon;

class Billy
{
    protected static $accessToken;
    protected static $instance;

    public static function initialize($dbRow)
    {
        self::$accessToken = $dbRow['api_key'];
        self::$instance = new Billy();
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function request($method, $url, $body = null)
    {
        $headers = ["X-Access-Token: " . self::$accessToken];
        $c = curl_init("https://api.billysbilling.com/v2" . $url);
        curl_setopt($c, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        if ($body) {
            curl_setopt($c, CURLOPT_POSTFIELDS, json_encode($body));
            $headers[] = "Content-Type: application/json";
        }
        curl_setopt($c, CURLOPT_HTTPHEADER, $headers);
        $res = curl_exec($c);
        $body = json_decode($res);
        $info = curl_getinfo($c);
        return (object)[
            'status' => $info['http_code'],
            'body' => $body
        ];
    }

    public static function createInvoice($params)
    {
        $realParams = [
            'invoice' => [
                'organizationId' => 'ACx42GkURdCdQFFweX7VDQ',
                'contactId' => $params['contactId'],
                'paymentTermsDays' => 8,
                'currencyId' => $params['Currency'],
                'entryDate' => Carbon::now()->format('Y-m-d'),
                'lines' => [

                ]
            ]
        ];
        foreach ($params['ProductLines'] as $productLine) {
            $realParams['invoice']['lines'][] = [
                'unitPrice' => $productLine['BaseAmountValue'],
                'productId' => 'Ccx9WbbORtGTQtRX48Sdtg',
                'description' => $productLine['Description']
            ];
        }

        $res = self::request("POST", "/invoices", $realParams);

        return $res;
    }

    public function getContacts()
    {
        $res = self::request("GET", "/contacts");

        $results = [];
        $i = 0;
        foreach ($res->body->contacts as $contact) {
            $results[$i]['name'] = $contact->name;
            $results[$i]['guid'] = $contact->id;
            $i++;
        }

        return $results;
    }
}
