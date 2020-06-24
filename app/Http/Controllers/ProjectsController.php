<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Client;
use App\Models\Status;
use App\Models\Project;
use App\Models\Integration;
use App\Models\Document;
use App\Services\Storage\GetStorageProvider;
use Illuminate\Http\Request;
use Datatables;
use Carbon\Carbon;
use App\Http\Requests\Project\StoreProjectRequest;
use Ramsey\Uuid\Uuid;
use App\Repositories\FilesystemIntegration\FilesystemIntegration;

class ProjectsController extends Controller
{
    const CREATED = 'created';
    const UPDATED_STATUS = 'updated_status';
    const UPDATED_TIME = 'updated_time';
    const UPDATED_ASSIGN = 'updated_assign';
    const UPDATED_DEADLINE = 'updated_deadline';

    public function indexData()
    {
        $projects = Project::with(['assignee', 'status'])->select(
            ['external_id', 'title', 'created_at', 'deadline', 'user_assigned_id', 'status_id']
        )->get();

        return Datatables::of($projects)
            ->addColumn('titlelink', function ($projects) {
                return '<a href="projects/' . $projects->external_id . '" ">' . $projects->title . '</a>';
            })
            ->editColumn('created_at', function ($projects) {
                return $projects->created_at ? with(new Carbon($projects->created_at))
                    ->format(carbonDate()) : '';
            })
            ->editColumn('deadline', function ($projects) {
                return $projects->created_at ? with(new Carbon($projects->deadline))
                    ->format(carbonDate()) : '';
            })
            ->editColumn('user_assigned_id', function ($projects) {
                return $projects->assignee->name;
            })
            ->editColumn('status_id', function ($projects) {
                return '<span class="label label-success" style="background-color:' . $projects->status->color . '"> ' .
                    $projects->status->title . '</span>';
            })
            ->addColumn('view', function ($projects) {
                return '<a href="' . route("projects.show", $projects->external_id) . '" class="btn btn-link">' . __('View') .'</a>';
            })
            ->rawColumns(['titlelink','view', 'status_id'])
            ->make(true);
    }

    public function index()
    {
        return view('projects.index')
        ->withStatuses(Status::typeOfProject()->get());
    }

    public function update()
    {
    }

    /**
     * @param StoreTaskRequest $request
     * @return mixed
     */
    public function store(StoreProjectRequest $request) // uses __contrust request
    {
        if ($request->client_external_id) {
            $client = Client::whereExternalId($request->client_external_id);
        }
        $project = Project::create(
            [
                'title' => $request->title,
                'description' => clean($request->description),
                'user_assigned_id' => $request->user_assigned_id,
                'deadline' => Carbon::parse($request->deadline),
                'status_id' => $request->status_id,
                'user_created_id' => auth()->id(),
                'external_id' => Uuid::uuid4()->toString(),
                'client_id' => Client::whereExternalId($client->external_id)->first()->id,
            ]
        );

        $insertedExternalId = $project->external_id;

        Session()->flash('flash_message', __('Project successfully added'));
        event(new \App\Events\ProjectAction($project, self::CREATED));

        if (!is_null($request->images)) {
            foreach ($request->file('images') as $image) {
                $this->upload($image, $project);
            }
        }

        //Hack to make dropzone js work, as it only called with AJAX and not form submit
        return response()->json(['project_external_id' => $project->external_id]);
    }

    private function upload($image, $project)
    {
        if (!auth()->user()->can('task-upload-files')) {
            session()->flash('flash_message_warning', __('You do not have permission to upload images'));
            return redirect()->route('tasks.show', $project->external_id);
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

        $folder = $project->external_id;
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
            'integration_type' => get_class($fileSystem)
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return mixed
     */
    public function create($client_external_id = null)
    {
        $client =  Client::whereExternalId($client_external_id);

        return view('projects.create')
            ->withUsers(User::with(['department'])->get()->pluck('nameAndDepartmentEagerLoading', 'id'))
            ->withClients(Client::pluck('company_name', 'external_id'))
            ->withClient($client ?: null)
            ->withStatuses(Status::typeOfProject()->pluck('title', 'id'))
            ->with('filesystem_integration', Integration::whereApiType('file')->first());
    }

    public function show(Project $project)
    {
        $tasks = $project->tasks->count();
        if ($tasks === 0) {
            $completionPercentage = 0;
        } else {
            $completedTasks = $project->tasks()->whereHas('status', function ($q) {
                $q->where('title', 'closed');
            })->count();
            $completionPercentage = round($completedTasks / $tasks * 100);
        }



        $collaborators = collect();

        $collaborators->push($project->assignee);
        foreach ($project->tasks as $task) {
            $collaborators->push($task->user);
        }


        return view('projects.show')
            ->withProject($project)
            ->withStatuses(Status::typeOfTask()->get())
            ->withTasks($project->tasks)
            ->withCompletionPercentage($completionPercentage)
            ->withCollaborators($collaborators->unique())
            ->withUsers(User::with(['department'])->get()->pluck('nameAndDepartmentEagerLoading', 'id'))
            ->withFiles($project->documents)
            ->with('filesystem_integration', Integration::whereApiType('file')->first());
        ;
    }

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
        $project = $this->findByExternalId($external_id);
        $project->fill($input)->save();

        event(new \App\Events\ProjectAction($project, self::UPDATED_STATUS));
        Session()->flash('flash_message', __('Task status is updated'));

        return redirect()->back();
    }

    public function updateAssign($external_id, Request $request)
    {
        $project = Project::with('assignee')->whereExternalId($external_id)->first();

        $user_assigned_id= $request->user_assigned_id;

        $project->user_assigned_id = $user_assigned_id;
        $project->save();

        event(new \App\Events\ProjectAction($project, self::UPDATED_ASSIGN));

        Session()->flash('flash_message', __('New user is assigned'));
        return redirect()->back();
    }

    /**
     * Update the follow up date (Deadline)
     * @param UpdateLeadFollowUpRequest $request
     * @param $external_id
     * @return mixed
     */
    public function updateDeadline(Request $request, $external_id)
    {
        if (!auth()->user()->can('task-update-deadline')) {
            session()->flash('flash_message_warning', __('You do not have permission to change task deadline'));
            return redirect()->route('tasks.show', $external_id);
        }
        $project = $this->findByExternalId($external_id);
        $input = $request->all();
        $input = $request =
            ['deadline' => $request->deadline_date . " " . $request->deadline_time . ":00"];
        $project->fill($input)->save();
        event(new \App\Events\ProjectAction($project, self::UPDATED_DEADLINE));
        Session()->flash('flash_message', __('New deadline is set'));
        return redirect()->back();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function findByExternalId($external_id)
    {
        return Project::whereExternalId($external_id)->first();
    }
}
