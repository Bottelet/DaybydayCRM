<?php

namespace App\Services\Storage;

use App\Repositories\FilesystemIntegration\FilesystemIntegration;

class Local implements FilesystemIntegration
{
    public function isEnabled()
    {
        return config('app.env') === 'testing' || config('app.env') === 'local';
    }

    public function upload($client_folder, $filename, $file): array
    {
        return [
            'file_path' => $filename,
            'id'        => $filename,
        ];
    }

    public function delete($full_path): bool
    {
        return true;
    }

    public function view($file)
    {
        // In testing/local environments, return fake file content
        if (config('app.env') === 'testing' || config('app.env') === 'local') {
            return 'fake file content';
        }

        // TODO: Implement actual view() method for production
    }

    public function download($file)
    {
        // In testing/local environments, return fake file content
        if (config('app.env') === 'testing' || config('app.env') === 'local') {
            return 'fake file content';
        }

        // TODO: Implement actual download() method for production
    }

    public function revokeAccess()
    {
        // TODO: Implement revokeAccess() method.
    }
}
