<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Document;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Task;
use App\Services\Storage\GetStorageProvider;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;
use Session;

class DocumentsController extends Controller
{
    /**
     * Source types that support assignable ownership checks
     */
    private const ASSIGNABLE_TYPES = [Task::class, Project::class, Lead::class];

    public function __construct()
    {
        $this->middleware('filesystem.is.enabled');
    }

    public function view($external_id)
    {
        // Eager load the source and nested client relationships to avoid N+1 queries
        $document = Document::with(['sourceable', 'sourceable.client'])->whereExternalId($external_id)->first();

        if (! $document) {
            abort(404);
        }

        // Check if user has permission to view document via source ownership
        if (! $this->canAccessDocument($document)) {
            session()->flash('flash_message_warning', __('You do not have permission to view this document'));

            return redirect()->back();
        }

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
        // Eager load the source and nested client relationships to avoid N+1 queries
        $document = Document::with(['sourceable', 'sourceable.client'])->whereExternalId($external_id)->first();

        if (! $document) {
            abort(404);
        }

        // Check if user has permission to download document via source ownership
        if (! $this->canAccessDocument($document)) {
            session()->flash('flash_message_warning', __('You do not have permission to download this document'));

            return redirect()->back();
        }

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
        /**   if (!auth()->user()->can('image-upload')) {
        session()->flash('flash_message_warning', __('You do not have permission to upload images'));
        return redirect()->route('tasks.show', $task->external_id);
        }**/
        $task = Task::whereExternalId($external_id)->first();

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
        /**   if (!auth()->user()->can('image-upload')) {
        session()->flash('flash_message_warning', __('You do not have permission to upload images'));
        return redirect()->route('tasks.show', $task->external_id);
        }**/
        $project = Project::whereExternalId($external_id)->first();

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

    /**
     * Check if the authenticated user can access the document
     * User can access document if they are assigned to or created the source resource
     * or if they have ownership of the associated client
     *
     * @param  Document  $document
     * @return bool
     */
    private function canAccessDocument($document)
    {
        $user = auth()->user();

        // Use the morphTo relationship to get the source model
        $source = $document->sourceable;

        if (! $source) {
            return false;
        }

        // For Client source type, check user_id
        if ($document->source_type === Client::class) {
            return $source->user_id === $user->id;
        }

        // For Task, Project, and Lead - check creator, assignee, or client ownership
        if (in_array($document->source_type, self::ASSIGNABLE_TYPES)) {
            return $this->userOwnsAssignableSource($source, $user);
        }

        return false;
    }

    /**
     * Check if user owns an assignable source (Task, Project, Lead)
     * via creation, assignment, or client ownership
     *
     * @param  mixed  $source
     * @param  \App\Models\User  $user
     * @return bool
     */
    private function userOwnsAssignableSource($source, $user)
    {
        // Check if user created the source
        if (! is_null($source->user_created_id) && $source->user_created_id === $user->id) {
            return true;
        }

        // Check if user is assigned to the source
        if (! is_null($source->user_assigned_id) && $source->user_assigned_id === $user->id) {
            return true;
        }

        // Check if user owns the client associated with the source
        if ($source->client && ! is_null($source->client->user_id) && $source->client->user_id === $user->id) {
            return true;
        }

        return false;
    }
}
