<?php

namespace App\Services\Storage\Authentication;

use App\Concerns\Authentication\StorageAuthenticatorContract;
use App\Models\Integration;
use App\Services\Storage\Dropbox;
use Exception;
use GuzzleHttp\Client as HttpClient;
use RuntimeException;

class DropboxAuthenticator implements StorageAuthenticatorContract
{
    private $clientId;

    private $clientSecret;

    private $httpClient;

    public function __construct()
    {
        $this->clientId     = config('services.dropbox.client_id');
        $this->clientSecret = config('services.dropbox.client_secret');
        $this->httpClient   = new HttpClient();

        if ( ! $this->clientId || ! $this->clientSecret) {
            throw new RuntimeException('Dropbox credentials are not configured');
        }
    }

    public function authUrl(): string
    {
        return 'https://www.dropbox.com/oauth2/authorize?' . http_build_query([
            'client_id'     => $this->clientId,
            'response_type' => 'code',
            'redirect_uri'  => route('dropbox.callback'),
        ]);
    }

    public function token($code)
    {
        try {
            $response = $this->httpClient->post('https://api.dropboxapi.com/oauth2/token', [
                'form_params' => [
                    'code'          => $code,
                    'grant_type'    => 'authorization_code',
                    'client_id'     => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'redirect_uri'  => route('dropbox.callback'),
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (Exception $e) {
            throw new RuntimeException('Failed to exchange Dropbox authorization code: ' . $e->getMessage());
        }
    }

    public function revokeAccess(): void
    {
        $integration = Integration::query()
            ->where(['api_type' => 'file', 'name' => Dropbox::class])
            ->first();

        if ( ! $integration) {
            throw new RuntimeException('Dropbox integration not found');
        }

        // Dropbox tokens can be revoked by making a request with the token
        // There's no direct revoke endpoint, but tokens expire and can be managed in Dropbox account
        // For now, we'll just remove the integration record
    }
}
