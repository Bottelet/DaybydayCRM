<?php

namespace App\Services\Storage;

use App\Models\Integration;
use App\Repositories\FilesystemIntegration\FilesystemIntegration;

/**
 * @deprecated Use StorageAdapterRegistry via dependency injection instead.
 *
 * This class is kept for backward compatibility.  All new code should inject
 * StorageAdapterRegistry and call ->driver() on it.
 */
class GetStorageProvider
{
    private static $storageProviders = [
        'local'       => Local::class,
        'dropbox'     => Dropbox::class,
        'googledrive' => GoogleDrive::class,
    ];

    /**
     * @deprecated inject StorageAdapterRegistry instead
     */
    public static function getStorage(): FilesystemIntegration
    {
        return app(StorageAdapterRegistry::class)->driver();
    }

    public static function fromIntegration(?Integration $integration): FilesystemIntegration
    {
        if (app()->environment('testing') || (app()->environment('local') && config('storage.force_local', true))) {
            return new Local();
        }

        return new (self::providerClassFromIntegration($integration))();
    }

    public static function providerClassFromIntegration(?Integration $integration): string
    {
        if ($integration) {
            $providerName = mb_strtolower($integration->name);

            return self::$storageProviders[$providerName] ?? Local::class;
        }

        return Local::class;
    }
}
