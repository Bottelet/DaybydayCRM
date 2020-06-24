<?php
namespace App\Services\Storage\Authentication;

use App\Models\Integration;
use App\Services\Storage\GoogleDrive;
use Google_Client;
use Google_Service_Drive;

class GoogleDriveAuthenticator implements StorageAuthenticatorContract
{
    private $client;

    public function __construct()
    {
        $auth = [
            'client_id' => config('services.google-drive.client_id'),
            'client_secret' => config('services.google-drive.client_secret')
        ];
        $this->client = new Google_Client();
        $this->client->setAuthConfig($auth);
        $this->client->setAccessType("offline");        // offline access
        $this->client->setIncludeGrantedScopes(true);   // incremental auth
        $this->client->addScope(
            Google_Service_Drive::DRIVE_FILE
        );
        $this->client->setRedirectUri(route('googleDrive.callback'));
    }


    public function authUrl()
    {
        return $this->client->createAuthUrl();
    }

    public function token($code)
    {
        $this->client->fetchAccessTokenWithAuthCode($code);

        return $this->client->getAccessToken();
    }

    public function revokeAccess()
    {
        $token = Integration::where(['api_type' => 'file', 'name' => GoogleDrive::class])->first()->api_key;
        $this->client->fetchAccessTokenWithRefreshToken($token);
        return $this->client->revokeToken($token);
    }
}
