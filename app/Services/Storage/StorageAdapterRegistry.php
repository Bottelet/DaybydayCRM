<?php

namespace App\Services\Storage;

use App\Models\Integration;
use App\Repositories\FilesystemIntegration\FilesystemIntegration;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Registry that resolves the active filesystem/storage adapter.
 *
 * Replaces the anti-pattern of calling the static
 * GetStorageProvider::getStorage() service-locator throughout controllers,
 * observers and middleware.
 *
 * Usage (via dependency injection):
 *
 *   public function __construct(private StorageAdapterRegistry $storage) {}
 *
 *   $this->storage->driver()->upload(...);
 *   if ($this->storage->isEnabled()) { ... }
 */
class StorageAdapterRegistry
{
    /** @var array<string, class-string<FilesystemIntegration>> */
    private static array $providerMap = [
        'local'       => Local::class,
        'dropbox'     => Dropbox::class,
        'googledrive' => GoogleDrive::class,
    ];

    private ?FilesystemIntegration $resolved = null;

    /**
     * Return the active storage adapter.
     *
     * - In testing environments a FakeStorageAdapter is returned when one has
     *   been bound in the container; otherwise the Local adapter is used.
     * - When `STORAGE_FORCE_LOCAL=true` (default for local env) the Local
     *   adapter is returned regardless of the database configuration.
     * - Falls back gracefully to NullStorageAdapter when nothing else works.
     */
    public function driver(): FilesystemIntegration
    {
        if ($this->resolved !== null) {
            return $this->resolved;
        }

        // Allow tests to inject a fake via the container.
        if (app()->bound(FilesystemIntegration::class)) {
            return $this->resolved = app(FilesystemIntegration::class);
        }

        if (app()->environment('testing')) {
            return $this->resolved = new Local();
        }

        if (app()->environment('local') && config('storage.force_local', true)) {
            return $this->resolved = new Local();
        }

        $integration = Integration::whereApiType('file')->first();

        if ( ! $integration) {
            return $this->resolved = new Local();
        }

        $providerName = mb_strtolower($integration->name);
        $class        = self::$providerMap[$providerName] ?? null;

        if ($class === null) {
            Log::warning('StorageAdapterRegistry: unknown storage provider', ['name' => $integration->name]);

            return $this->resolved = new NullStorageAdapter();
        }

        try {
            return $this->resolved = app($class);
        } catch (Throwable $e) {
            Log::warning('StorageAdapterRegistry: could not instantiate adapter', [
                'class' => $class,
                'error' => $e->getMessage(),
            ]);

            return $this->resolved = new NullStorageAdapter();
        }
    }

    /**
     * Convenience proxy – delegates to the resolved driver.
     */
    public function isEnabled(): bool
    {
        return $this->driver()->isEnabled();
    }

    /**
     * Reset the in-memory cached adapter.  Useful in tests.
     */
    public function reset(): void
    {
        $this->resolved = null;
    }
}
