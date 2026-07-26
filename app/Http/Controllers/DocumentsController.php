<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Document;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\Storage\StorageAdapterRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

class DocumentsController extends Controller
{
    /**
     * Source types that support assignable ownership checks.
     */
    private const ASSIGNABLE_TYPES = [Task::class, Project::class, Lead::class];

    public function __construct(private StorageAdapterRegistry $storage)
    {
        $this->middleware('filesystem.is.enabled');
    }

    public function view($external_id)
    {
        // Eager load the source and conditionally load client for assignable types
        $document = Document::with(['source' => function ($query) {
            // Only eager load client for types that have a client relationship
            if (in_array(get_class($query->getModel()), self::ASSIGNABLE_TYPES)) {
                $query->with('client');
            }
        }])->whereExternalId($external_id)->first();

        if ( ! $document) {
            abort(404);
        }

        // Check if user has permission to view document via source ownership
        if ( ! $this->canAccessDocument($document)) {
            if (request()->expectsJson()) {
                abort(403, __('You do not have permission to view this document'));
            }

            session()->flash('flash_message_warning', __('You do not have permission to view this document'));

            return redirect()->back();
        }

        $fileSystem = $this->storage->driver();
        $file       = $fileSystem->view($document);

        if ( ! $file) {
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
        // Eager load the source and conditionally load client for assignable types
        $document = Document::with(['source' => function ($query) {
            // Only eager load client for types that have a client relationship
            if (in_array(get_class($query->getModel()), self::ASSIGNABLE_TYPES)) {
                $query->with('client');
            }
        }])->whereExternalId($external_id)->first();

        if ( ! $document) {
            abort(404);
        }

        // Check if user has permission to download document via source ownership
        if ( ! $this->canAccessDocument($document)) {
            if (request()->expectsJson()) {
                abort(403, __('You do not have permission to download this document'));
            }

            session()->flash('flash_message_warning', __('You do not have permission to download this document'));

            return redirect()->back();
        }

        $fileSystem = $this->storage->driver();
        $file       = $fileSystem->download($document);

        if ( ! $file) {
            session()->flash('flash_message_warning', __('File does not exists, make sure it has not been moved from dropbox (:path)', ['path' => $document->path]));

            return redirect()->back();
        }

        return response($file, 200)
            ->header('Content-Type', $document->mime)
            ->header('Content-Disposition', 'attachment')
            ->header('filename', $document->original_filename);
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function upload(Request $request, $external_id)
    {
        if ( ! auth()->user()->can('document-upload')) {
            if ($request->expectsJson()) {
                return response()->json(['message' => __('You do not have permission to upload a document')], 403);
            }
            session()->flash('flash_message_warning', __('You do not have permission to upload a document'));

            return redirect()->route('tasks.show', $external_id);
        }
        $client = Client::whereExternalId($external_id)->first();

        $file        = $request->file('file');
        $filename    = Str::random(8) . '_' . $file->getClientOriginalName();
        $fileOrginal = $file->getClientOriginalName();

        $size       = $file->getSize();
        $mbsize     = $size / 1048576;
        $totaltsize = mb_substr($mbsize, 0, 4);

        if ($totaltsize > 15) {
            session()->flash('flash_message', __('File Size cannot be bigger than 15MB'));

            return redirect()->back();
        }

        $client_folder = $client->external_id;
        $fileSystem    = $this->storage->driver();
        $fileData      = $fileSystem->upload($client_folder, $filename, $file);
        $input         = [
            'external_id'       => Uuid::uuid4()->toString(),
            'path'              => $fileData['file_path'],
            'size'              => $totaltsize,
            'original_filename' => $fileOrginal,
            'source_id'         => $client->id,
            'source_type'       => Client::class,
            'mime'              => $file->getClientMimeType(),
            'integration_id'    => $fileData['id'] ?? null,
            'integration_type'  => get_class($fileSystem),
        ];
        Document::query()->create($input);
        session()->flash('flash_message', __('File successfully uploaded'));
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function uploadToTask(Request $request, $external_id)
    {
        if ( ! auth()->user()->can('task-upload-files')) {
            session()->flash('flash_message_warning', __('You do not have permission to upload files'));

            return redirect()->back();
        }

        $task = Task::whereExternalId($external_id)->first();

        if ( ! $task) {
            session()->flash('flash_message_warning', __('Task not found'));

            return redirect()->back();
        }

        if (null !== $request->files) {
            foreach ($request->file('files') as $image) {
                $file        = $image;
                $filename    = Str::random(8) . '_' . $file->getClientOriginalName();
                $fileOrginal = $file->getClientOriginalName();

                $size       = $file->getSize();
                $mbsize     = $size / 1048576;
                $totaltsize = mb_substr($mbsize, 0, 4);

                if ($totaltsize > 15) {
                    session()->flash('flash_message', __('File Size cannot be bigger than 15MB'));

                    return redirect()->back();
                }

                $folder     = $external_id;
                $fileSystem = $this->storage->driver();
                $fileData   = $fileSystem->upload($folder, $filename, $file);

                Document::query()->create([
                    'external_id'       => Uuid::uuid4()->toString(),
                    'path'              => $fileData['file_path'],
                    'size'              => $totaltsize,
                    'original_filename' => $fileOrginal,
                    'source_id'         => $task->id,
                    'source_type'       => Task::class,
                    'mime'              => $file->getClientMimeType(),
                    'integration_id'    => $fileData['id'] ?? null,
                    'integration_type'  => get_class($fileSystem),
                ]);
            }
        }
        session()->flash('flash_message', __('File successfully uploaded'));

        return response()->json(['external_id' => $task->external_id], 200);
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function uploadToProject(Request $request, $external_id)
    {
        if ( ! auth()->user()->can('project-upload-files')) {
            session()->flash('flash_message_warning', __('You do not have permission to upload files'));

            return redirect()->back();
        }

        $project = Project::whereExternalId($external_id)->first();

        if ( ! $project) {
            session()->flash('flash_message_warning', __('Project not found'));

            return redirect()->back();
        }

        if (null !== $request->files) {
            foreach ($request->file('files') as $image) {
                $file        = $image;
                $filename    = Str::random(8) . '_' . $file->getClientOriginalName();
                $fileOrginal = $file->getClientOriginalName();

                $size       = $file->getSize();
                $mbsize     = $size / 1048576;
                $totaltsize = mb_substr($mbsize, 0, 4);

                if ($totaltsize > 15) {
                    session()->flash('flash_message', __('File Size cannot be bigger than 15MB'));

                    return redirect()->back();
                }

                $folder = $external_id;

                $fileSystem = $this->storage->driver();

                $fileData = $fileSystem->upload($folder, $filename, $file);

                Document::query()->create([
                    'external_id'       => Uuid::uuid4()->toString(),
                    'path'              => $fileData['file_path'],
                    'size'              => $totaltsize,
                    'original_filename' => $fileOrginal,
                    'source_id'         => $project->id,
                    'source_type'       => Project::class,
                    'mime'              => $file->getClientMimeType(),
                    'integration_id'    => $fileData['id'] ?? null,
                    'integration_type'  => get_class($fileSystem),
                ]);
            }
        }
        session()->flash('flash_message', __('File successfully uploaded'));

        return response()->json(['external_id' => $project->external_id], 200);
    }

    public function destroy($external_id)
    {
        if ( ! auth()->user()->can('document-delete')) {
            session()->flash('flash_message_warning', __('You do not have permission to delete a document'));

            return redirect()->route('tasks.show', $external_id);
        }

        $document = Document::whereExternalId($external_id)->first();

        if ( ! $document) {
            session()->flash('flash_message_warning', __('Document not found'));

            return redirect()->back();
        }

        // Observer will handle file deletion
        $document->delete();

        session()->flash('flash_message', __('File has been deleted'));

        return redirect()->back();
    }

    /**
     * Opens invoce line creation modal.
     *
     * @param $external_id Customer's external_id
     *
     * @return View
     */
    public function uploadFilesModalView(Request $request, $external_id, $type)
    {
        $view = view('documents._uploadFileModal');

        if ($type == 'task') {
            $task  = Task::whereExternalId($external_id)->first();
            $title = $task->title;
        } elseif ($type == 'client') {
            // Client has no "task" relation (it has many tasks()) — the entity
            // itself is what's being uploaded to here, and "title" means its
            // company name, not an unrelated task's title.
            $task  = Client::whereExternalId($external_id)->first();
            $title = $task->company_name;
        } elseif ($type == 'project') {
            $task  = Project::whereExternalId($external_id)->first();
            $title = $task->title;
        }

        // The client upload route is registered as "document.upload" (nested under
        // the clients/ prefix group), not "document.client.upload" — task/project
        // do follow the "document.{type}.upload" pattern.
        $uploadRouteName = $type === 'client' ? 'document.upload' : 'document.' . $type . '.upload';

        return $view
            ->withTitle($title)
            ->with('external_id', $external_id)
            ->withType($type)
            ->withRoute(route($uploadRouteName, $external_id));
    }

    /**
     * Check if the authenticated user can access the document
     * User can access document if they are assigned to or created the source resource
     * or if they have ownership of the associated client.
     *
     * @param Document $document
     *
     * @return bool
     */
    private function canAccessDocument($document)
    {
        $user = auth()->user();

        $source = $document->source;

        if ( ! $source) {
            return false;
        }

        if ($document->source_type === Client::class) {
            return $source->user_id === $user->id;
        }

        if (in_array($document->source_type, self::ASSIGNABLE_TYPES)) {
            return $this->userOwnsAssignableSource($source, $user);
        }

        return false;
    }

    /**
     * Check if user owns an assignable source (Task, Project, Lead)
     * via creation, assignment, or client ownership.
     *
     * @param mixed $source
     * @param User  $user
     *
     * @return bool
     */
    private function userOwnsAssignableSource($source, $user)
    {
        // Check if user created the source
        if (null !== $source->user_created_id && $source->user_created_id === $user->id) {
            return true;
        }

        // Check if user is assigned to the source
        if (null !== $source->user_assigned_id && $source->user_assigned_id === $user->id) {
            return true;
        }

        // Check if user owns the client associated with the source
        return (bool) ($source->client && null !== $source->client->user_id && $source->client->user_id === $user->id);
    }
}
