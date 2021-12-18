<?php
namespace App\Services\Storage\Authentication;

use App\Models\Integration;
use App\Services\Storage\Dropbox;
use GuzzleHttp\Client;

class DropboxAuthenticator implements StorageAuthenticatorContract
{
    private $client_id;
    private $redirect_uri;
    private $client_secret;
    private $client;

    public function __construct()
    {
        $this->client_id = config('services.dropbox.client_id');
        $this->client_secret = config('services.dropbox.client_secret');
        $this->redirect_uri = route('dropbox.callback');
        $this->client = new Client();
    }


    public function authUrl()
    {
        return "https://www.dropbox.com/oauth2/authorize?client_id=" . $this->client_id . "&response_type=code&redirect_uri=" . $this->redirect_uri . "";
    }

    public function token($code)
    {
        $res = $this->client->request('POST', 'https://api.dropboxapi.com/oauth2/token', [
            'form_params' => [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'redirect_uri' => $this->redirect_uri
            ]
        ]);

        return json_decode($res->getBody()->read(1024));
    }

    public function getRefreshToken($oldToken)
    {
        $res = $this->client->request('POST', 'https://api.dropboxapi.com/oauth2/token', [
            'form_params' => [
                'grant_type' => 'refresh_token',
                'code' => $oldToken,
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'redirect_uri' => $this->redirect_uri
            ]
        ]);

        return json_decode($res->getBody()->read(1024));
    }

    public function revokeAccess()
    {
        $token = optional(Integration::whereApiType('file')->whereName(Dropbox::class)->first())->api_key;

        $this->client->request('POST', 'https://api.dropboxapi.com/2/auth/token/revoke', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token
            ]
        ]);
        return true;
    }
}
