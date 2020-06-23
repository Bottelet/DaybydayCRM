<?php
namespace App\Services\Storage;

use App\Services\Storage\Authentication\DropboxAuthenticator;
use Storage;
use File;
use GuzzleHttp\Client;
use App\Repositories\FilesystemIntegration\FilesystemIntegration;
use App\Models\Integration;


class Dropbox implements FilesystemIntegration
{
    private $disk;

    public function __construct()
    {
        $this->disk = Storage::disk('dropbox');
    }

    public function upload($folder, $filename, $file): array
    {
        $file_path = FilesystemIntegration::ROOT_FOLDER . '/' .$folder . '/' . $filename;
    	$this->disk->put($file_path, File::get($file));

        return [
            'file_path' => $file_path
        ];
    }

    public function delete($file): bool
    {
    	return $this->disk->delete($file->path);
    }

    public function get($file)
    {
        if(!$this->disk->exists($file->path)) {
            return null;
        };
        return $this->disk->get($file->path);
    }

    public function revokeAccess()
    {
        app(DropboxAuthenticator::class)->revokeAccess();
    }

    public function view($file)
    {
        return $this->get($file);
    }

    public function download($file)
    {
        return $this->get($file);
    }

    public function isEnabled()
    {
        return true;
    }
}
