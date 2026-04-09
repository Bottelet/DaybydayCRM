<?php

namespace App\Services\Storage;

use App\Models\Integration;

class GetStorageProvider
{
    private static $storageProviders = [
        'local' => Local::class,
        'dropbox' => Dropbox::class,
        'googledrive' => GoogleDrive::class,
    ];

    public static function getStorage()
    {
        $integration = Integration::where('api_type', 'file')->first();
        if ($integration) {
            $providerName = strtolower($integration->name);
            $className = self::$storageProviders[$providerName] ?? Local::class;

            return new $className;
        } else {
            return new Local;
        }
    }
}
