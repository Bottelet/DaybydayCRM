<?php
namespace App\Services\Storage;

use App\Services\Storage\Authentication\GoogleDriveAuthenticator;
use Storage;
use File;
use GuzzleHttp\Client;
use App\Repositories\FilesystemIntegration\FilesystemIntegration;
use Google_Client;
use Google_Service_Drive;
use App\Models\Integration;

class GoogleDrive implements FilesystemIntegration
{
    private $client;
    private $driveService;

    public function __construct()
    {
        $auth = [
            'client_id' => config('services.google-drive.client_id'),
            'client_secret' => config('services.google-drive.client_secret')
        ];
        $this->client = new Google_Client;
        $this->client->setAuthConfig($auth);
        $this->client->setRedirectUri(route('googleDrive.callback'));
        $this->client->setAccessType("offline");
        $this->client->setScopes(array('https://www.googleapis.com/auth/drive.file'));
        $this->client->fetchAccessTokenWithRefreshToken(Integration::where(['name' => get_class($this)])->first()->api_key);

        $this->driveService = new \Google_Service_Drive($this->client);
    }

    public function upload($folder, $filename, $file): array
    {
        $files = $this->driveService->files->listFiles(["q" => "mimeType = 'application/vnd.google-apps.folder' and trashed=false"]);
        $rootFolderId = null;
        foreach ($files['files'] as $item) {
            if ($item['name'] == 'Daybyday') {
                $rootFolder = $item;
                $rootFolderId = $item['id'];
                break;
            }
        }

        if (!$rootFolderId) {
            $rootFolderBluePrint = new \Google_Service_Drive_DriveFile(
                [
                    'name' => 'Daybyday',
                    'mimeType' => 'application/vnd.google-apps.folder'
                ]
            );
            $rootFolder =  $this->driveService->files->create($rootFolderBluePrint, array(
                //'data' => $content,
                'mimeType' => 'image/jpeg',
                'uploadType' => 'multipart',
                'fields' => 'id'));

            $clientFolderBluePrint = new \Google_Service_Drive_DriveFile(
                [
                    'name' => $folder,
                    'mimeType' => 'application/vnd.google-apps.folder',
                    'parents' => [$rootFolder['id']]
                ]
            );

            $clientFolder = $this->driveService->files->create($clientFolderBluePrint, array(
                'mimeType' => 'image/jpeg',
                'uploadType' => 'multipart',
                'fields' => 'id'));
        } else {
            $clientFolder = null;
            $files = $this->driveService->files->listFiles(
                ["q" => "'" . $rootFolder['id'] . "'" . " in parents and mimeType = 'application/vnd.google-apps.folder' and trashed=false"]
            );

            foreach ($files['files'] as $item) {
                if ($item['name'] == $folder) {
                    $clientFolder = $item;
                    break;
                }
            }
            if (!$clientFolder) {
                $clientFolderBluePrint = new \Google_Service_Drive_DriveFile(
                    [
                        'name' => $folder,
                        'mimeType' => 'application/vnd.google-apps.folder',
                        'parents' => [$rootFolder['id']]
                    ]
                );

                $clientFolder = $this->driveService->files->create($clientFolderBluePrint, array(
                    'mimeType' => 'image/jpeg',
                    'uploadType' => 'multipart',
                    'fields' => 'id'));
            }
        }

        $fileMetadata = new \Google_Service_Drive_DriveFile(
            [
                'name' => $filename,
                'parents' => [$clientFolder['id']],
            ]
        );
        $content = file_get_contents($file);
        $file = $this->driveService->files->create($fileMetadata, array(
            'data' => $content,
            'uploadType' => 'multipart',
            'fields' => 'id'));

        return [
            'id' => $file['id'],
            'file_path' => FilesystemIntegration::ROOT_FOLDER . '/' . $folder . '/' . $filename
        ];
    }

    public function delete($file): bool
    {
        try {
            $this->driveService->files->delete($file->integration_id);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function get($file, $options)
    {
        $file = $this->driveService->files->get($file->integration_id, $options);

        if (!$file) {
            session()->flash('flash_message_warning', __('File does not exists, make sure it has not been moved from google drive (:path)', ['path' => $file->path]));
            return redirect()->back();
        }

        return $file;
    }


    /**
     * @return bool
     * @throws \Google_Exception
     */
    public function revokeAccess()
    {
        app(GoogleDriveAuthenticator::class)->revokeAccess();
    }

    public function view($file)
    {
        $link = $this->get($file, ['alt' => 'media']);
        return $link->getBody()->getContents();
    }

    public function download($file)
    {
        $link = $this->get($file, ['alt' => 'media']);
        return $link->getBody()->getContents();
    }

    public function isEnabled()
    {
        return true;
    }
}
