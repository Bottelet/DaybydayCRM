<?php
namespace App\Services\Storage\Authentication;

interface StorageAuthenticatorContract
{
    public function authUrl();

    public function token($code);

    public function revokeAccess();
}
