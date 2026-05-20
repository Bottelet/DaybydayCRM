<?php

namespace App\Services\Storage;

use App\Repositories\FilesystemIntegration\FilesystemIntegration;

/**
 * Null object implementation of FilesystemIntegration.
 *
 * Used when:
 *  - No filesystem integration is configured.
 *  - Running in the test environment with the null driver.
 *  - A configured adapter fails to boot.
 *
 * `isEnabled()` returns false so that callers that check the enabled state
 * can gate storage-specific features appropriately.
 */
class NullStorageAdapter implements FilesystemIntegration
{
    public function isEnabled(): bool
    {
        return false;
    }

    public function upload($client_folder, $filename, $file): array
    {
        return [
            'file_path' => $filename,
            'id'        => null,
        ];
    }

    public function delete($full_path): bool
    {
        return true;
    }

    public function view($file) {}

    public function download($file) {}

    public function revokeAccess()
    {
        // No-op.
    }
}
