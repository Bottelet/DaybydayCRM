<?php

namespace App\Http\Controllers;

use App\Enums\PermissionName;
use App\Enums\ProjectStatus;
use App\Events\ProjectAction;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectAssignRequest;
use App\Http\Requests\Project\UpdateProjectDeadlineRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Requests\Project\UpdateProjectStatusRequest;
use App\Models\Client;
use App\Models\Document;
use App\Models\Integration;
use App\Models\Project;
use App\Models\Status;
use App\Models\User;
use App\Services\Project\ProjectService;
use App\Services\Storage\GetStorageProvider;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

class ProjectsController extends Controller
{
    public const CREATED = 'created';

    public const UPDATED_STATUS = 'updated_status';

    public const UPDATED_TIME = 'updated_time';

    public const UPDATED_ASSIGN = 'updated_assign';

    public const UPDATED_DEADLINE = 'updated_deadline';

    public function __construct(private ProjectService $projectService)
    {
        $this->middleware(function ($request, $next) {
            if ( ! auth()->check() || ! auth()->user()->can('project-delete')) {
                abort(403);
            }

            return $next($request);
        }, ['only' => ['destroy']]);

        $this->middleware(function ($request, $next) {
            if ( ! auth()->check() || ! auth()->user()->can('can-assign-new-user-to-project')) {
                if ($request->expectsJson()) {
                    abort(403, __('You do not have permission to assign users to this project'));
                }

                session()->flash('flash_message_warning', __('You do not have permission to assign users to this project'));

                return redirect()->back();
            }

            return $next($request);
        }, ['only' => ['updateAssign']]);
    }

    public function indexData()
    {
        $projects = Project::with(['assignee', 'status', 'client'])
            ->leftJoin('statuses', 'projects.status_id', '=', 'statuses.id')
            ->leftJoin('clients', 'projects.client_id', '=', 'clients.id')
            ->leftJoin('users', 'projects.user_assigned_id', '=', 'users.id')
            ->select(
                ['projects.external_id', 'projects.title', 'projects.created_at', 'projects.deadline', 'projects.user_assigned_id', 'projects.status_id', 'projects.client_id']
            )
            ->orderBy('projects.deadline', 'desc')
            ->orderBy('statuses.title', 'asc')
            ->orderBy('clients.company_name', 'asc')
            ->orderBy('users.name', 'asc');

        return Datatables::of($projects)
            ->addColumn('titlelink', function ($projects) {
                return '<a href="' . route('projects.show', [$projects->external_id]) . '">' . e($projects->title) . '</a>';
            })
            ->editColumn('client', function ($projects) {
                return $projects->client->company_name;
            })
            ->editColumn('deadline', function ($projects) {
                return $projects->created_at ? with(new Carbon($projects->deadline))
                    ->format(carbonDate()) : '';
            })
            ->editColumn('created_at', function ($projects) {
                return $projects->created_at ? with(new Carbon($projects->created_at))
                    ->format(carbonDate()) : '';
            })
            ->editColumn('user_assigned_id', function ($projects) {
                return $projects->assignee->name;
            })
            ->editColumn('status_id', function ($projects) {
                return '<span class="label label-success" style="background-color:' . $projects->status->color . '"> '
                    . $projects->status->title . '</span>';
            })
            ->addColumn('view', function ($projects) {
                return '<a href="' . route('projects.show', $projects->external_id) . '" class="btn btn-link">' . __('View') . '</a>'
                . '<a data-toggle="modal" data-id="' . route('projects.destroy', $projects->external_id) . '" data-title="' . $projects->title . '" data-target="#deletion" class="btn btn-link">' . __('Delete') . '</a>';
            })
            ->rawColumns(['titlelink', 'view', 'status_id'])
            ->make(true);
    }

    public function index()
    {
        return view('projects.index')
            ->withStatuses(Status::typeOfProject()->get());
    }

    public function destroy(Project $project, Request $request)
    {
        if ( ! auth()->user()->can('project-delete')) {
            session()->flash('flash_message_warning', __('You do not have permission to delete projects'));
            if ($request->expectsJson()) {
                return response()->json(['message' => __('You do not have permission to delete projects')], 403);
            }

            return redirect()->back();
        }

        $deleteTasks = $request->delete_tasks ? true : false;
        if ($project->tasks && $deleteTasks) {
            $project->tasks()->delete();
        } else {
            $project->tasks()->update(['project_id' => null]);
        }

        $project->delete();
        session()->flash('flash_message', __('Project deleted'));

        if ($request->expectsJson()) {
            return response()->json(['message' => __('Project deleted')], 200);
        }

        return redirect()->back();
    }

    /**
     * @param StoreTaskRequest $request
     *
     * @return mixed
     */
    public function store(StoreProjectRequest $request)
    {
        try {
            $project = $this->projectService->create($request->validated(), auth()->id());
        } catch (Throwable $exception) {
            report($exception);

            return $this->failureResponse(
                $request,
                __('Project could not be created. Please try again.'),
                'project'
            );
        }

        if ( ! $project) {
            return $this->failureResponse($request, __('Could not find client'), 'project', 404);
        }

        event(new ProjectAction($project, self::CREATED));

        if (null !== $request->images) {
            foreach ($request->file('images') as $image) {
                $this->upload($image, $project);
            }
        }

        // Flash before the JSON early-return: the real browser create form submits
        // via AJAX (expectsJson() is true, see the dropzone/AJAX hack below) and
        // then does a client-side redirect to the show page, so the flash must be
        // set here to survive that navigation.
        session()->flash('flash_message', __('Project created'));

        // Hack to make dropzone js work, as it only called with AJAX and not form submit
        if ($request->expectsJson()) {
            return response()->json(['project_external_id' => $project->external_id]);
        }

        return redirect()->route('projects.index');
    }

    public function edit($external_id)
    {
        if ( ! auth()->user()->can(PermissionName::PROJECT_UPDATE->value)) {
            session()->flash('flash_message_warning', __('You do not have permission to update projects'));

            return redirect()->route('projects.show', $external_id);
        }

        return view('projects.edit')->withProject($this->findByExternalId($external_id));
    }

    public function update($external_id, UpdateProjectRequest $request)
    {
        $project = $this->findByExternalId($external_id);
        $this->projectService->update($project, $request->validated());
        session()->flash('flash_message', __('Project successfully updated'));

        return redirect()->route('projects.show', $project->external_id);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return mixed
     */
    public function create($client_external_id = null)
    {
        $client = $client_external_id ? Client::whereExternalId($client_external_id)->first() : null;

        return view('projects.create')
            ->withUsers(User::with(['department'])->get()->pluck('nameAndDepartmentEagerLoading', 'id'))
            ->withClients(Client::query()->pluck('company_name', 'external_id'))
            ->withClient($client)
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
                $q->where(function ($statusQuery) {
                    $statusQuery->whereRaw('LOWER(title) = ?', [mb_strtolower(ProjectStatus::CLOSED->value)]);
                });
            })->count();
            $completionPercentage = round($completedTasks / $tasks * 100);
        }

        $preparedShowData = $this->projectService->prepareShowCollaboratorsAndTasks($project);

        return view('projects.show')
            ->withProject($project)
            ->withClient($project->client)
            ->withStatuses(Status::typeOfProject()->get())
            ->withTasks($preparedShowData['tasks'])
            ->withCompletionPercentage($completionPercentage)
            ->withCollaborators($preparedShowData['collaborators'])
            ->withUsers(User::with(['department'])->get()->pluck('nameAndDepartmentEagerLoading', 'id'))
            ->withFiles($project->documents)
            ->with('filesystem_integration', Integration::whereApiType('file')->first());
    }

    public function updateStatus($external_id, UpdateProjectStatusRequest $request)
    {
        $project            = $this->findByExternalId($external_id);
        $project->status_id = $request->validated('status_id');
        $project->save();

        event(new ProjectAction($project, self::UPDATED_STATUS));
        session()->flash('flash_message', __('Project status updated'));

        if ($request->ajax()) {
            return response('', 302)->header('X-Redirect', url()->previous() ?: '/');
        }

        return redirect()->back();
    }

    public function updateAssign($external_id, UpdateProjectAssignRequest $request)
    {
        $project = Project::with('assignee')->whereExternalId($external_id)->firstOrFail();
        $this->projectService->assign($project, $request->validated('user_assigned_id'));

        event(new ProjectAction($project, self::UPDATED_ASSIGN));

        session()->flash('flash_message', __('New user is assigned'));

        return redirect()->back();
    }

    /**
     * Update the follow up date (Deadline).
     *
     * @param UpdateLeadFollowUpRequest $request
     *
     * @return mixed
     */
    public function updateDeadline(UpdateProjectDeadlineRequest $request, $external_id)
    {
        if ( ! auth()->user()->can('project-update-deadline')) {
            session()->flash('flash_message_warning', __('You do not have permission to change project deadline'));

            return redirect()->route('projects.show', $external_id);
        }

        $project = $this->findByExternalId($external_id);
        $this->projectService->updateDeadline(
            $project,
            $request->validated('deadline_date')
        );
        event(new ProjectAction($project, self::UPDATED_DEADLINE));
        session()->flash('flash_message', __('New deadline is set'));

        return redirect()->back();
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function findByExternalId($external_id)
    {
        return Project::whereExternalId($external_id)->firstOrFail();
    }

    private function upload($image, $project)
    {
        if ( ! auth()->user()->can('project-upload-files')) {
            session()->flash('flash_message_warning', __('You do not have permission to upload images'));

            return redirect()->route('tasks.show', $project->external_id);
        }
        $file        = $image;
        $filename    = Str::random(8) . '_' . $file->getClientOriginalName();
        $fileOrginal = $file->getClientOriginalName();

        $size       = $file->getSize();
        $mbsize     = $size / 1048576;
        $totaltsize = mb_substr($mbsize, 0, 4);

        if ($totaltsize > 15) {
            Session::flash('flash_message', __('File Size cannot be bigger than 15MB'));

            return redirect()->back();
        }

        $folder     = $project->external_id;
        $fileSystem = GetStorageProvider::getStorage();
        $fileData   = $fileSystem->upload($folder, $filename, $file);

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
