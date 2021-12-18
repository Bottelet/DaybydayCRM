<?php
namespace App\Services\Storage;

use App\Models\Integration;
use Illuminate\Support\Facades\File;
use Spatie\Dropbox\Client as DropboxClient;
use App\Services\Storage\Authentication\DropboxAuthenticator;
use App\Repositories\FilesystemIntegration\FilesystemIntegration;

class Dropbox implements FilesystemIntegration
{
    private $client;

    public function __construct()
    {
        $dropbox_integration = Integration::where('name', Dropbox::class)->first();

        if (!$dropbox_integration) {
            throw new \Exception('Dropbox integration is not configured');
        }
       
        /** @var DropboxClient $client */
        $this->client = new DropboxClient($dropbox_integration->api_key);
    }

    public function upload($folder, $filename, $file): array
    {
        $file_path = FilesystemIntegration::ROOT_FOLDER . '/' .$folder . '/' . $filename;
        $this->client->upload($file_path, File::get($file));

        return [
            'file_path' => $file_path
        ];
    }

    public function delete($file): bool
    {
        $this->client->delete($file->path);

        return true;
    }

    public function get($file)
    {
        // if (!$this->client->exists($file->path)) {
        //     return null;
        // };
   
        return $this->client->download($file->path);
    }

    public function revokeAccess()
    {
        app(DropboxAuthenticator::class)->revokeAccess();
    }

    public function view($file)
    {
        return stream_get_contents($this->get($file));
    }

    public function download($file)
    {
        return stream_get_contents($this->client->download($file->path));
    }

    public function isEnabled()
    {
        return true;
    }
}
