<?php
namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\Storage\GetStorageProvider;
use Gate;
use Carbon;
use Datatables;
use File;
use App\Models\Task;
use App\Models\Client;
use App\Models\User;
use App\Models\Setting;
use App\Http\Requests;
use App\Models\Status;
use App\Models\Integration;
use Illuminate\Http\Request;
use App\Http\Requests\Task\StoreTaskRequest;
use Ramsey\Uuid\Uuid;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Project;

class TasksController extends Controller
{
    const CREATED = 'created';
    const UPDATED_STATUS = 'updated_status';
    const UPDATED_TIME = 'updated_time';
    const UPDATED_ASSIGN = 'updated_assign';
    const UPDATED_DEADLINE = 'updated_deadline';

    protected $invoices;

    public function __construct()
    {
        $this->middleware('filesystem.is.enabled', ['only' => ['upload']]);
        $this->middleware('task.create', ['only' => ['create']]);
        $this->middleware('task.update.status', ['only' => ['updateStatus']]);
        $this->middleware('task.assigned', ['only' => ['updateAssign', 'updateTime']]);
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

        return Datatables::of($tasks)
            ->addColumn('titlelink', '<a href="{{ route("tasks.show",[$external_id]) }}">{{$title}}</a>')
            ->editColumn('client', function ($projects) {
                return $projects->client->company_name;
            })
            ->editColumn('created_at', function ($tasks) {
                return $tasks->created_at ? with(new Carbon($tasks->created_at))
                    ->format(carbonDate()) : '';
            })
            ->editColumn('deadline', function ($tasks) {
                return $tasks->created_at ? with(new Carbon($tasks->deadline))
                    ->format(carbonDate()) : '';
            })
            ->editColumn('user_assigned_id', function ($tasks) {
                return $tasks->user->name;
            })
            ->editColumn('status_id', function ($tasks) {
                return '<span class="label label-success" style="background-color:' . $tasks->status->color . '"> ' .
                    $tasks->status->title . '</span>';
            })
            ->addColumn('view', function ($tasks) {
                return '<a href="' . route("tasks.show", $tasks->external_id) . '" class="btn btn-link">' . __('View') .'</a>'
                . '<a data-toggle="modal" data-id="'. route('tasks.destroy',$tasks->external_id) . '" data-target="#deletion" class="btn btn-link">' . __('Delete') .'</a>'
                ;
            })
            ->rawColumns(['titlelink','view', 'status_id'])
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
        $client =  Client::whereExternalId($client_external_id);
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
     * @param StoreTaskRequest $request
     * @return mixed
     */
    public function store(StoreTaskRequest $request) // uses __contrust request
    {
        $project = null;
        if ($request->client_external_id) {
            $client = Client::whereExternalId($request->client_external_id);
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
            'client_id' => $client->id,
            'project_id' => optional($project)->id
        ]
        );

        $insertedExternalId = $task->external_id;

        Session()->flash('flash_message', __('Task successfully added'));
        event(new \App\Events\TaskAction($task, self::CREATED));

        if (!is_null($request->images)) {
            foreach ($request->file('images') as $image) {
                $this->upload($image, $task);
            }
        }
        //Hack to make dropzone js work, as it only called with AJAX and not form submit
        return response()->json(['task_external_id' => $task->external_id, 'project_external_id' => $project ? $project->external_id : null]);
        return redirect()->route("tasks.show", $insertedExternalId);
    }

    public function destroy(Task $task, Request $request)
    {
        $deleteInvoice = $request->delete_invoice ? true : false;

        if($task->invoice && $deleteInvoice) {
            $task->invoice()->delete();
        } elseif ($task->invoice) {
            $task->invoice->removeReference();
        }
        $task->delete();
        
        Session()->flash('flash_message', __('Task deleted'));
        return redirect()->back();
    }

    private function upload($image, $task)
    {
        if (!auth()->user()->can('task-upload-files')) {
            session()->flash('flash_message_warning', __('You do not have permission to upload images'));
            return redirect()->route('tasks.show', $task->external_id);
        }
        $file = $image;
        $filename = str_random(8) . '_' . $file->getClientOriginalName();
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
            'integration_type' => get_class($fileSystem)
        ]);
    }

    /**
     * @param Request $request
     * @param $external_id
     * @return mixed
     * @throws \Exception
     */
    public function show(Request $request, $external_id)
    {
        $task = $this->findByExternalId($external_id);
        if (!$task) {
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
     * @param $external_id
     * @param Request $request
     * @return
     * @internal param $ [Auth]  $external_id Checks Logged in users id
     * @internal param $ [Model] $task->user_assigned_id Checks the id of the user assigned to the task
     * If Auth and user_id allow complete else redirect back if all allowed excute
     * else stmt
     */
    public function updateStatus($external_id, Request $request)
    {
        if (!auth()->user()->can('task-update-status')) {
            session()->flash('flash_message_warning', __('You do not have permission to change task status'));
            return redirect()->route('tasks.show', $external_id);
        }
        $input = $request->all();

        if ($request->ajax() && isset($input["statusExternalId"])) {
            $input["status_id"] = Status::whereExternalId($input["statusExternalId"])->first()->id;
        }

        $task = $this->findByExternalId($external_id);
        $task->fill($input)->save();
        event(new \App\Events\TaskAction($task, self::UPDATED_STATUS));
        Session()->flash('flash_message', __('Task status is updated'));

        return redirect()->back();
    }

    public function updateProject($external_id, Request $request)
    {
        $task = $this->findByExternalId($external_id);
        $project_id = null;

        if ($request->project_external_id) {
            $project_id = Project::whereExternalId($request->project_external_id)->first()->id;
        }

        $task->fill([
            'project_id' => $project_id
        ])->save();


        //event(new \App\Events\TaskAction($task, self::UPDATED_STATUS));
        Session()->flash('flash_message', __('Task project is updated'));

        return redirect()->back();
    }

    /**
     * @param $external_id
     * @param Request $request
     * @return mixed
     */
    public function updateAssign($external_id, Request $request)
    {
        $task = Task::with('user')->whereExternalId($external_id)->first();

        $user_assigned_id = $request->user_assigned_id;

        $task->user_assigned_id = $user_assigned_id;
        $task->save();
        $task->refresh();

        event(new \App\Events\TaskAction($task, self::UPDATED_ASSIGN));
        Session()->flash('flash_message', __('New user is assigned'));
        return redirect()->back();
    }

    /**
     * Update the follow up date (Deadline)
     * @param Request $request
     * @param $external_id
     * @return mixed
     */
    public function updateDeadline(Request $request, $external_id)
    {
        if (!auth()->user()->can('task-update-deadline')) {
            session()->flash('flash_message_warning', __('You do not have permission to change task deadline'));
            return redirect()->route('tasks.show', $external_id);
        }
        $task = $this->findByExternalId($external_id);
        $task->fill(['deadline' => Carbon::parse($request->deadline_date)])->save();

        event(new \App\Events\TaskAction($task, self::UPDATED_DEADLINE));
        Session()->flash('flash_message', 'New deadline is set');
        return redirect()->back();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function findByExternalId($external_id)
    {
        return Task::whereExternalId($external_id)->firstOrFail();
    }

    /**
     * Remove the specified resource from storage.
     * @return mixed
     * @internal param int $id
     */
    public function marked()
    {
        Notifynder::readAll(\Auth::id());
        return redirect()->back();
    }
}
