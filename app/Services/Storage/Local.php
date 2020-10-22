<?php
namespace App\Services\Storage;

use App\Models\Document;
use App\Repositories\FilesystemIntegration\FilesystemIntegration;
use Illuminate\Support\Facades\Storage;

class Local implements FilesystemIntegration
{
    public function isEnabled()
    {
        return true;
    }

    public function upload($client_folder, $filename, $file): array
    {
        Storage::disk('local')->put($client_folder, $file);
        
        return ['file_path' => $client_folder . '/' . $file->hashName()];
    }

    public function delete($full_path): bool
    {
        return Storage::disk('local')->delete($full_path);
    }

    public function view($file)
    {
        return $this->download($file);
    }

    public function download($file)
    {
        $fileData =  Storage::disk('local')->get($file->path);
        return $fileData;
    }

    public function revokeAccess()
    {
        // TODO: Implement revokeAccess() method.
    }
}
