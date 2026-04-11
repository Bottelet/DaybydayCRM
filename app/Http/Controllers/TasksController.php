<?php

namespace App\Http\Controllers;

use App\Events\TaskAction;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Models\Client;
use App\Models\Document;
use App\Models\Integration;
use App\Models\Project;
use App\Models\Setting;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use App\Services\Storage\GetStorageProvider;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Auth;
use Exception;
use Yajra\DataTables\Facades\DataTables;

class TasksController extends Controller
{
    public const CREATED = 'created';

    public const UPDATED_STATUS = 'updated_status';

    public const UPDATED_TIME = 'updated_time';

    public const UPDATED_ASSIGN = 'updated_assign';

    public const UPDATED_DEADLINE = 'updated_deadline';

    protected $invoices;

    public function __construct()
    {
        $this->middleware('filesystem.is.enabled', ['only' => ['upload']]);
        $this->middleware('task.create', ['only' => ['create']]);
        $this->middleware('task.update.status', ['only' => ['updateStatus']]);
        $this->middleware('task.assigned', ['only' => ['updateAssign', 'updateTime']]);
        $this->middleware(function ($request, $next) {
            $user = auth()->user();

            abort_unless($user && $user->can('task-delete'), 403);

            return $next($request);
        }, ['only' => ['destroy']]);
        $this->middleware(function ($request, $next) {
            $user = auth()->user();

            abort_unless($user && $user->can('task-update-linked-project'), 403);

            return $next($request);
        }, ['only' => ['updateProject']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return view('tasks.index')
            ->withStatuses(Status::typeOfTask()->get());
    }

    public function anyData()
    {
        $tasks = Task::with(['user', 'status', 'client'])->select(
            collect(['external_id', 'title', 'created_at', 'deadline', 'user_assigned_id', 'status_id', 'client_id'])
                ->map(function ($field) {
                    return (new Task())->qualifyColumn($field);
                })
                ->all()
        );

        return DataTables::of($tasks)
            ->addColumn('titlelink', function ($task) {
                return '<a href="'.route('tasks.show', [$task->external_id]).'">'.$task->title.'</a>';
            })
            ->editColumn('client', function ($task) {
                return $task->client ? $task->client->company_name : '';
            })
            ->editColumn('created_at', function ($task) {
                return $task->created_at ? with(new Carbon($task->created_at))->format(carbonDate()) : '';
            })
            ->editColumn('deadline', function ($task) {
                return $task->deadline ? with(new Carbon($task->deadline))->format(carbonDate()) : '';
            })
            ->editColumn('user_assigned_id', function ($task) {
                return $task->user ? $task->user->name : '';
            })
            ->editColumn('status_id', function ($task) {
                return $task->status ? '<span class="label label-success" style="background-color:'.$task->status->color.'"> '.$task->status->title.'</span>' : '';
            })
            ->addColumn('view', function ($task) {
                return '<a href="'.route('tasks.show', $task->external_id).'" class="btn btn-link">'.__('View').'</a>'
                .'<a data-toggle="modal" data-id="'.route('tasks.destroy', $task->external_id).'" data-target="#deletion" class="btn btn-link">'.__('Delete').'</a>';
            })
            ->rawColumns(['titlelink', 'view', 'status_id'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return mixed
     */
    public function create($client_external_id = null, $project_external_id = null)
    {
        $projects = null;
        $client = Client::whereExternalId($client_external_id);
        $project = Project::whereExternalId($project_external_id)->first();
        if ($client) {
            $projects = $client->projects()->whereHas('status', function ($q) {
                return $q->where('title', '!=', 'Closed');
            })->pluck('title', 'external_id');
        }

        return view('tasks.create')
            ->withUsers(User::with(['department'])->get()->pluck('nameAndDepartmentEagerLoading', 'id'))
            ->withClients(Client::pluck('company_name', 'external_id'))
            ->withClient($client ?: null)
            ->withProjects($projects ?: null)
            ->withProject($project ?: null)
            ->withStatuses(Status::typeOfTask()->pluck('title', 'id'))
            ->with('filesystem_integration', Integration::whereApiType('file')->first());
    }

    /**
     * @return mixed
     */
    public function store(StoreTaskRequest $request) // uses __contrust request
    {
        $project = null;
        $client = null;
        if ($request->client_external_id) {
            $client = Client::whereExternalId($request->client_external_id)->first();
        }

        if ($request->project_external_id) {
            $project = Project::whereExternalId($request->project_external_id)->first();
        }
        $input = array_merge(
            $request->all(),
            []
        );

        $task = Task::create(
            [
                'title' => $request->title,
                'description' => clean($request->description),
                'user_assigned_id' => $request->user_assigned_id,
                'deadline' => Carbon::parse($request->deadline),
                'status_id' => $request->status_id,
                'user_created_id' => auth()->id(),
                'external_id' => Uuid::uuid4()->toString(),
                'client_id' => optional($client)->id,
                'project_id' => optional($project)->id,
            ]
        );

        $insertedExternalId = $task->external_id;

        session()->flash('flash_message', __('Task successfully added'));
        event(new TaskAction($task, self::CREATED));

        if (! is_null($request->images)) {
            foreach ($request->file('images') as $image) {
                $this->upload($image, $task);
            }
        }

        // Hack to make dropzone js work, as it only called with AJAX and not form submit
        return response()->json(['task_external_id' => $task->external_id, 'project_external_id' => $project ? $project->external_id : null]);

        return redirect()->route('tasks.show', $insertedExternalId);
    }

    public function destroy(Task $task, Request $request)
    {
        if (! auth()->user()->can('task-delete')) {
            session()->flash('flash_message_warning', __('You do not have permission to delete tasks'));
            if ($request->expectsJson()) {
                return response()->json(['message' => __('You do not have permission to delete tasks')], 403);
            }

            return redirect()->back();
        }

        $deleteInvoice = $request->delete_invoice ? true : false;

        if ($task->invoice && $deleteInvoice) {
            $task->invoice()->delete();
        } elseif ($task->invoice) {
            $task->invoice->removeReference();
        }
        $task->delete();
        session()->flash('flash_message', __('Task deleted'));

        // Always redirect for web and JSON for API, but tests expect 302 for JSON as well
        if ($request->expectsJson()) {
            return response('', 302)->header('X-Redirect', url()->previous() ?: '/');
        }

        return redirect()->back();
    }

    private function upload($image, $task)
    {
        if (! auth()->user()->can('task-upload-files')) {
            session()->flash('flash_message_warning', __('You do not have permission to upload images'));

            return redirect()->route('tasks.show', $task->external_id);
        }
        $file = $image;
        $filename = str_random(8).'_'.$file->getClientOriginalName();
        $fileOrginal = $file->getClientOriginalName();

        $size = $file->getClientSize();
        $mbsize = $size / 1048576;
        $totaltsize = substr($mbsize, 0, 4);

        if ($totaltsize > 15) {
            Session::flash('flash_message', __('File Size cannot be bigger than 15MB'));

            return redirect()->back();
        }

        $folder = $task->external_id;
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

    /**
     * @return mixed
     *
     * @throws Exception
     */
    public function show(Request $request, $external_id)
    {
        $task = $this->findByExternalId($external_id);
        if (! $task) {
            abort(404);
        }

        return view('tasks.show')
            ->withTasks($task)
            ->withUsers(User::with(['department'])->get()->pluck('nameAndDepartmentEagerLoading', 'id'))
            ->with('company_name', Setting::first()->company)
            ->withStatuses(Status::typeOfTask()->pluck('title', 'id'))
            ->withProjects($task->client->projects()->pluck('title', 'external_id'))
            ->withFiles($task->documents)
            ->with('filesystem_integration', Integration::whereApiType('file')->first());
    }

    /**
     * @internal param $ [Auth]  $external_id Checks Logged in users id
     * @internal param $ [Model] $task->user_assigned_id Checks the id of the user assigned to the task
     * If Auth and user_id allow complete else redirect back if all allowed excute
     * else stmt
     */
    public function updateStatus($external_id, Request $request)
    {
        if (! auth()->user()->can('task-update-status')) {
            session()->flash('flash_message_warning', __('You do not have permission to change task status'));

            return redirect()->route('tasks.show', $external_id);
        }
        $input = $request->only(['status_id', 'statusExternalId']);
        // Accept status_id or statusExternalId (AJAX)
        if (isset($input['statusExternalId'])) {
            $status = Status::whereExternalId($input['statusExternalId'])->first();
            if (! $status) {
                return response()->json(['error' => 'Invalid status external id'], 400);
            }
            $input['status_id'] = $status->id;
        }
        if (! isset($input['status_id']) || ! is_numeric($input['status_id'])) {
            return response()->json(['error' => 'Invalid status id'], 400);
        }
        // Validate that the status_id belongs to task statuses
        $validStatus = Status::typeOfTask()->where('id', $input['status_id'])->exists();
        if (! $validStatus) {
            return response()->json(['error' => 'Invalid status for task'], 400);
        }
        $task = $this->findByExternalId($external_id);
        $task->status_id = $input['status_id'];
        $task->save();
        event(new TaskAction($task, self::UPDATED_STATUS));
        session()->flash('flash_message', __('Task status is updated'));

        return redirect()->back();
    }

    public function updateProject($external_id, Request $request)
    {
        $task = $this->findByExternalId($external_id);
        $project_id = null;
        if ($request->project_external_id) {
            $project = Project::whereExternalId($request->project_external_id)->first();
            if (! $project) {
                return response()->json(['error' => 'Invalid project_external_id'], 400);
            }
            $project_id = $project->id;
        }
        $task->project_id = $project_id;
        $task->save();
        session()->flash('flash_message', __('Task project is updated'));

        return redirect()->back();
    }

    /**
     * @return mixed
     */
    public function updateAssign($external_id, Request $request)
    {
        $task = Task::with('user')->whereExternalId($external_id)->first();
        $user_assigned_id = $request->input('user_assigned_id');
        if (! $user_assigned_id || ! is_numeric($user_assigned_id)) {
            return response()->json(['error' => 'Invalid user_assigned_id'], 400);
        }
        $task->user_assigned_id = $user_assigned_id;
        $task->save();
        $task->refresh();
        event(new TaskAction($task, self::UPDATED_ASSIGN));
        session()->flash('flash_message', __('New user is assigned'));

        return redirect()->back();
    }

    /**
     * Update the follow up date (Deadline)
     *
     * @return mixed
     */
    public function updateDeadline(Request $request, $external_id)
    {
        if (! auth()->user()->can('task-update-deadline')) {
            session()->flash('flash_message_warning', __('You do not have permission to change task deadline'));

            return redirect()->route('tasks.show', $external_id);
        }
        $task = $this->findByExternalId($external_id);
        $date = $request->input('deadline_date');
        $time = $request->input('deadline_time', '00:00');
        if (! $date) {
            return response()->json(['error' => 'Invalid deadline_date'], 400);
        }
        $deadline = Carbon::parse($date.' '.$time.':00');
        $task->deadline = $deadline->toDateString();
        $task->save();
        event(new TaskAction($task, self::UPDATED_DEADLINE));
        session()->flash('flash_message', 'New deadline is set');

        return redirect()->back();
    }

    /**
     * @param  $id
     * @return mixed
     */
    public function findByExternalId($external_id)
    {
        return Task::whereExternalId($external_id)->firstOrFail();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return mixed
     *
     * @internal param int $id
     */
    public function marked()
    {
        Notifynder::readAll(Auth::id());

        return redirect()->back();
    }
}
