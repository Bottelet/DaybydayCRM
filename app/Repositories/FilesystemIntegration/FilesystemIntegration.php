<?php
namespace App\Repositories\FilesystemIntegration;

interface FilesystemIntegration
{
    const ROOT_FOLDER = "Daybyday";

    public function upload($client_folder, $filename, $file): array;

    public function delete($full_path): bool;

    public function view($file);

    public function download($file);

    public function revokeAccess();

    public function isEnabled();
}
