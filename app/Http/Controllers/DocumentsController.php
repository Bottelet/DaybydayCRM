<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Document;
use App\Models\Project;
use App\Models\Task;
use App\Services\Storage\GetStorageProvider;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;
use Session;

class DocumentsController extends Controller
{
    public function __construct()
    {
        $this->middleware('filesystem.is.enabled');
    }

    public function view($external_id)
    {
        $document = Document::whereExternalId($external_id)->first();
        $fileSystem = GetStorageProvider::getStorage();
        $file = $fileSystem->view($document);
        if (! $file) {
            session()->flash('flash_message_warning', __('File does not exists, make sure it has not been moved from dropbox (:path)', ['path' => $document->path]));

            return redirect()->back();
        }

        return response($file, 200)
            ->header('Content-Type', $document->mime)
            ->header('Content-Disposition', 'inline')
            ->header('filename', $document->original_filename);
    }

    public function download($external_id)
    {
        $document = Document::whereExternalId($external_id)->first();
        $fileSystem = GetStorageProvider::getStorage();
        $file = $fileSystem->download($document);

        if (! $file) {
            session()->flash('flash_message_warning', __('File does not exists, make sure it has not been moved from dropbox (:path)', ['path' => $document->path]));

            return redirect()->back();
        }

        return response($file, 200)
            ->header('Content-Type', $document->mime)
            ->header('Content-Disposition', 'attachment')
            ->header('filename', $document->original_filename);
    }

    /**
     * @param  $id
     * @return mixed
     */
    public function upload(Request $request, $external_id)
    {
        if (! auth()->user()->can('document-upload')) {
            session()->flash('flash_message_warning', __('You do not have permission to upload a document'));

            return redirect()->route('tasks.show', $external_id);
        }
        $client = Client::whereExternalId($external_id)->first();

        $file = $request->file('file');
        $filename = str_random(8).'_'.$file->getClientOriginalName();
        $fileOrginal = $file->getClientOriginalName();

        $size = $file->getSize();
        $mbsize = $size / 1048576;
        $totaltsize = substr($mbsize, 0, 4);

        if ($totaltsize > 15) {
            Session::flash('flash_message', __('File Size cannot be bigger than 15MB'));

            return redirect()->back();
        }

        $client_folder = $client->external_id;
        $fileSystem = GetStorageProvider::getStorage();
        $fileData = $fileSystem->upload($client_folder, $filename, $file);
        $input = array_replace(
            $request->all(),
            [
                'external_id' => Uuid::uuid4()->toString(),
                'path' => $fileData['file_path'],
                'size' => $totaltsize,
                'original_filename' => $fileOrginal,
                'source_id' => $client->id,
                'source_type' => Client::class,
                'mime' => $file->getClientMimeType(),
                'integration_id' => isset($fileData['id']) ? $fileData['id'] : null,
                'integration_type' => get_class($fileSystem),
            ]
        );
        Document::create($input);
        Session::flash('flash_message', __('File successfully uploaded'));
    }

    /**
     * @param  $id
     * @return mixed
     */
    public function uploadToTask(Request $request, $external_id)
    {
        $task = Task::whereExternalId($external_id)->first();

        if (! $task) {
            session()->flash('flash_message_warning', __('Task not found'));
            return redirect()->back();
        }

        if (! auth()->user()->can('task-upload-files')) {
            session()->flash('flash_message_warning', __('You do not have permission to upload files'));
            return redirect()->route('tasks.show', $external_id);
        }

        if (! is_null($request->files)) {
            foreach ($request->file('files') as $image) {
                $file = $image;
                $filename = str_random(8).'_'.$file->getClientOriginalName();
                $fileOrginal = $file->getClientOriginalName();

                $size = $file->getSize();
                $mbsize = $size / 1048576;
                $totaltsize = substr($mbsize, 0, 4);

                if ($totaltsize > 15) {
                    Session::flash('flash_message', __('File Size cannot be bigger than 15MB'));

                    return redirect()->back();
                }

                $folder = $external_id;
                $fileSystem = GetStorageProvider::getStorage();
                $fileData = $fileSystem->upload($folder, $filename, $file);

                Document::create([
                    'external_id' => Uuid::uuid4()->toString(),
                    'path' => $fileData['file_path'],
                    'size' => $totaltsize,
                    'original_filename' => $fileOrginal,
                    'source_id' => $task->id,
                    'source_type' => Task::class,
                    'mime' => $file->getClientMimeType(),
                    'integration_id' => isset($fileData['id']) ? $fileData['id'] : null,
                    'integration_type' => get_class($fileSystem),
                ]);
            }
        }
        Session::flash('flash_message', __('File successfully uploaded'));

        return $task->external_id;
    }

    /**
     * @param  $id
     * @return mixed
     */
    public function uploadToProject(Request $request, $external_id)
    {
        $project = Project::whereExternalId($external_id)->first();

        if (! $project) {
            session()->flash('flash_message_warning', __('Project not found'));
            return redirect()->back();
        }

        if (! auth()->user()->can('project-upload-files')) {
            session()->flash('flash_message_warning', __('You do not have permission to upload files'));
            return redirect()->route('projects.show', $external_id);
        }

        if (! is_null($request->files)) {
            foreach ($request->file('files') as $image) {
                $file = $image;
                $filename = str_random(8).'_'.$file->getClientOriginalName();
                $fileOrginal = $file->getClientOriginalName();

                $size = $file->getSize();
                $mbsize = $size / 1048576;
                $totaltsize = substr($mbsize, 0, 4);

                if ($totaltsize > 15) {
                    Session::flash('flash_message', __('File Size cannot be bigger than 15MB'));

                    return redirect()->back();
                }

                $folder = $external_id;

                $fileSystem = GetStorageProvider::getStorage();

                $fileData = $fileSystem->upload($folder, $filename, $file);

                Document::create([
                    'external_id' => Uuid::uuid4()->toString(),
                    'path' => $fileData['file_path'],
                    'size' => $totaltsize,
                    'original_filename' => $fileOrginal,
                    'source_id' => $project->id,
                    'source_type' => Project::class,
                    'mime' => $file->getClientMimeType(),
                    'integration_id' => isset($fileData['id']) ? $fileData['id'] : null,
                    'integration_type' => get_class($fileSystem),
                ]);
            }
        }
        Session::flash('flash_message', __('File successfully uploaded'));

        return $project->external_id;
    }

    public function destroy($external_id)
    {
        if (! auth()->user()->can('document-delete')) {
            session()->flash('flash_message_warning', __('You do not have permission to delete a document'));

            return redirect()->route('tasks.show', $external_id);
        }
        $fileSystem = GetStorageProvider::getStorage();

        $document = Document::whereExternalId($external_id)->first();
        $deleted = $fileSystem->delete($document);
        if (! $deleted) {
            Session()->flash('flash_message_warning', __("Something wen't wrong, we can't find the file on the cloud. But worry not, we delete what we know about the image"));
        } else {
            Session()->flash('flash_message', __('File has been deleted'));
        }
        $document->delete();

        return redirect()->back();
    }

    /**
     * Opens invoce line creation modal
     *
     * @param  $external_id  Customer's external_id
     * @return View
     */
    public function uploadFilesModalView(Request $request, $external_id, $type)
    {
        $view = view('documents._uploadFileModal');

        if ($type == 'task') {
            $task = Task::whereExternalId($external_id)->first();
        } elseif ($type == 'client') {
            $task = Client::whereExternalId($external_id)->first()->task;
        } elseif ($type == 'project') {
            $task = Project::whereExternalId($external_id)->first();
        }

        return $view
            ->withTitle($task->title)
            ->with('external_id', $external_id)
            ->withType($type)
            ->withRoute(route('document.'.$type.'.upload', $external_id));
    }
}
