<?php

namespace App\Services\Storage;

use App\Repositories\FilesystemIntegration\FilesystemIntegration;
use Illuminate\Support\Facades\Storage;

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

        if ( ! $file || ! isset($file->path) || ! Storage::exists($file->path)) {
            return;
        }

        return Storage::get($file->path);
    }

    public function download($file)
    {
        // In testing/local environments, return fake file content
        if (config('app.env') === 'testing' || config('app.env') === 'local') {
            return 'fake file content';
        }

        if ( ! $file || ! isset($file->path) || ! Storage::exists($file->path)) {
            return;
        }

        return Storage::get($file->path);
    }

    public function revokeAccess()
    {
        // TODO: Implement revokeAccess() method.
    }
}
