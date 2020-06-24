<?php
namespace App\Services\Storage;

use App\Models\Integration;

class GetStorageProvider
{
    public static function getStorage()
    {
        $integration = Integration::where('api_type', 'file')->first();
        if ($integration) {
            return new $integration->name;
        } else {
            return new Local();
        }
    }
}
