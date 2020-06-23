<?php
namespace App\Services\Storage;

use App\Repositories\FilesystemIntegration\FilesystemIntegration;

class Local implements FilesystemIntegration
{
    public function isEnabled()
    {
        return false;
    }

    public function upload($client_folder, $filename, $file): array
    {
        return [];
    }

    public function delete($full_path): bool
    {
        return true;
    }

    public function view($file)
    {
        // TODO: Implement view() method.
    }

    public function download($file)
    {
        // TODO: Implement download() method.
    }

    public function revokeAccess()
    {
        // TODO: Implement revokeAccess() method.
    }
}
