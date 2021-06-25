<?php
namespace App\Http\Controllers;

use Gate;
use Datatables;
use Illuminate\Support\Facades\Storage;
use Session;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Task;
use App\Models\Client;
use App\Models\Setting;
use App\Models\Status;
use App\Models\Lead;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Requests\User\StoreUserRequest;
use Ramsey\Uuid\Uuid;

class UsersController extends Controller
{
    protected $users;
    protected $roles;

    public function __construct()
    {
        $this->middleware('user.create', ['only' => ['create']]);
        $this->middleware('is.demo', ['only' => ['update', 'destroy']]);
    }

    /**
     * @return mixed
     */
    public function index()
    {
        return view('users.index')->withUsers(User::all());
    }

    public function calendarUsers()
    {
        if (!auth()->user()->can('absence-view')) {
            session()->flash('flash_message_warning', __('You do not have permission to view this page'));
            return redirect()->back();
        }
        return User::with(['department', 'absences' =>  function ($q) {
            return $q->whereBetween('start_at', [today()->subWeeks(2)->startOfDay(), today()->addWeeks(4)->endOfDay()])
                      ->orWhereBetween('end_at', [today()->subWeeks(2)->startOfDay(), today()->addWeeks(4)->endOfDay()]);
        }
        ])->get();
    }

    public function users()
    {
        return User::with(['department'])->get();
    }

    public function anyData()
    {
        $users = User::select(['id', 'external_id', 'name', 'email', 'primary_number']);
        return Datatables::of($users)
            ->addColumn('namelink', '<a href="{{ route("users.show",[$external_id]) }}">{{$name}}</a>')
            ->addColumn('view', function ($user) {
                return '<a href="' . route("users.show", $user->external_id) . '" class="btn btn-link">' . __('View') .'</a>';
            })
            ->addColumn('edit', function ($user) {
                return '<a href="' . route("users.edit", $user->external_id) . '" class="btn btn-link">' . __('Edit') .'</a>';
            })
//            ->addColumn('delete', function ($user) {
//                return '<button type="button" class="btn btn-link" data-client_id="' . $user->external_id . '" onClick="openModal(\'' . $user->external_id . '\')" id="myBtn">' . __('Delete') .'</button>';
//            })
            ->rawColumns(['namelink','view', 'edit', 'delete'])
            ->make(true);
    }

    /**
     * Json for Data tables
     * @param $id
     * @return mixed
     */
    public function taskData($id)
    {
        $tasks = Task::with(['status', 'client'])->select(
            ['id', 'external_id', 'title', 'created_at', 'deadline', 'user_assigned_id', 'client_id', 'status_id']
        )
            ->where('user_assigned_id', $id)->get();
        return Datatables::of($tasks)
            ->addColumn('titlelink', '<a href="{{ route("tasks.show",[$external_id]) }}">{{$title}}</a>')
            ->editColumn('created_at', function ($tasks) {
                return $tasks->created_at ? with(new Carbon($tasks->created_at))
                    ->format(carbonDate()) : '';
            })
            ->editColumn('deadline', function ($tasks) {
                return $tasks->deadline ? with(new Carbon($tasks->deadline))
                    ->format(carbonDate()) : '';
            })
            ->editColumn('status_id', function ($tasks) {
                return '<span class="label label-success" style="background-color:' . $tasks->status->color . '"> ' .$tasks->status->title . '</span>';
            })
            ->editColumn('client_id', function ($tasks) {
                return $tasks->client->primaryContact->name;
            })
            ->rawColumns(['titlelink','status_id'])
            ->make(true);
    }

    /**
     * Json for Data tables
     * @param $id
     * @return mixed
     */
    public function leadData($id)
    {
        $leads = Lead::with(['status', 'client'])->select(
            ['id', 'external_id', 'title', 'created_at', 'deadline', 'user_assigned_id', 'client_id', 'status_id']
        )
            ->where('user_assigned_id', $id)->get();
        return Datatables::of($leads)
            ->addColumn('titlelink', '<a href="{{ route("leads.show",[$external_id]) }}">{{$title}}</a>')
            ->editColumn('created_at', function ($leads) {
                return $leads->created_at ? with(new Carbon($leads->created_at))
                    ->format(carbonDate()) : '';
            })
            ->editColumn('deadline', function ($leads) {
                return $leads->deadline ? with(new Carbon($leads->deadline))
                    ->format(carbonDate()) : '';
            })
            ->editColumn('status_id', function ($leads) {
                return '<span class="label label-success" style="background-color:' . $leads->status->color . '"> ' .
                    $leads->status->title . '</span>';
            })
            ->editColumn('client_id', function ($tasks) {
                return $tasks->client->primaryContact->name;
            })
            ->rawColumns(['titlelink','status_id'])
            ->make(true);
    }

    /**
     * Json for Data tables
     * @param $id
     * @return mixed
     */
    public function clientData($id)
    {
        $clients = Client::select(['external_id', 'company_name', 'vat', 'address'])->where('user_id', $id);
        return Datatables::of($clients)
            ->addColumn('clientlink', function ($clients) {
                return '<a href="' . route('clients.show', $clients->external_id) . '">' . $clients->company_name . '</a>';
            })
            ->editColumn('created_at', function ($clients) {
                return $clients->created_at ? with(new Carbon($clients->created_at))
                    ->format(carbonDate()) : '';
            })
            ->rawColumns(['clientlink'])
            ->make(true);
    }


    /**
     * @return mixed
     */
    public function create()
    {
        return view('users.create')
            ->withRoles($this->allRoles()->pluck('display_name', 'id'))
            ->withDepartments(Department::pluck('name', 'id'));
    }

    /**
     * @param StoreUserRequest $userRequest
     * @return mixed
     */
    public function store(StoreUserRequest $request)
    {
        $settings = Setting::first();
        if (User::count() >= $settings->max_users) {
            Session::flash('flash_message_warning', __('Max number of users reached'));
            return redirect()->back();
        }
        $path = null;
        if ($request->hasFile('image_path')) {
            $file =  $request->file('image_path');

            $filename = str_random(8) . '_' . $file->getClientOriginalName() ;
            $path = Storage::put($settings->external_id, $file);
        }


        $user = new User();
        $user->name = $request->name;
        $user->external_id = Uuid::uuid4()->toString();
        $user->email = $request->email;
        $user->address = $request->address;
        $user->primary_number = $request->primary_number;
        $user->secondary_number = $request->secondary_number;
        $user->password = bcrypt($request->password);
        $user->image_path = $path;
        $user->language = $request->language == "dk" ?: "en";
        $user->save();
        $user->roles()->attach($request->roles);
        $user->department()->attach($request->departments);
        $user->save();

        Session::flash('flash_message', __('User successfully added'));
        return redirect()->route('users.index');
    }

    /**
     * @param $external_id
     * @return mixed
     */
    public function show($external_id)
    {
        /** @var User $user */
        $user = $this->findByExternalId($external_id);
        return view('users.show')
            ->withUser($user)
            ->withCompanyname(Setting::first()->company)
            ->with('task_statistics', $user->totalOpenAndClosedTasks($external_id))
            ->with('lead_statistics', $user->totalOpenAndClosedLeads($external_id))
            ->with('lead_statuses', Status::typeOfLead()->get())
            ->with('task_statuses', Status::typeOfTask()->get());
    }


    /**
     * @param $external_id
     * @return mixed
     */
    public function edit($external_id)
    {
        return view('users.edit')
            ->withUser($this->findByExternalId($external_id))
            ->withRoles($this->allRoles()->pluck('display_name', 'id'))
            ->withDepartments(Department::pluck('name', 'id'));
    }

    /**
     * @param $external_id
     * @param UpdateUserRequest $request
     * @return mixed
     */
    public function update($external_id, UpdateUserRequest $request)
    {
        $user = $this->findByExternalId($external_id);
        $password = bcrypt($request->password);
        $role = $request->roles;
        $department = $request->departments;

        if( !auth()->user()->canChangePasswordOn($user) ) {
            unset($request['password']);
        }


        if ($request->hasFile('image_path')) {
            $companyname = Setting::first()->external_id;
            $file =  $request->file('image_path');

            $filename = str_random(8) . '_' . $file->getClientOriginalName() ;

            $path = Storage::put($companyname, $file);
            if ($request->password == "") {
                $input =  array_replace($request->except('password'), ['image_path'=>"$path"]);
            } else {
                $input =  array_replace($request->all(), ['image_path'=>"$path", 'password'=>"$password"]);
            }
        } else {
            if ($request->password == "") {
                $input =  array_replace($request->except('password'));
            } else {
                $input =  array_replace($request->all(), ['password'=>"$password"]);
            }
        }

        $owners = User::whereHas('roles', function ($q) {
            $q->where('name', Role::OWNER_ROLE);
        })->get();

        $user->fill($input)->save();
        $role = $user->roles->first();
        if ($role && $role->name == Role::OWNER_ROLE && $owners->count() <= 1) {
            Session()->flash('flash_message_warning', __('Not able to change owner role, please choose a new owner first'));
        } else {
            if(auth()->user()->canChangeRole() ) {
                $user->roles()->sync([$request->roles]);
            }
        }
        $user->department()->sync([$department]);

        Session()->flash('flash_message', __('User successfully updated'));
        return redirect()->back();
    }

    /**
     * @param $external_id
     * @return mixed
     */
    public function destroy(Request $request, $external_id)
    {
        $user = $this->findByExternalId($external_id);

        if ($user->hasRole('owner')) {
            return Session()->flash('flash_message_warning', __('Not allowed to delete super admin'));
        }

        if ($request->tasks == "move_all_tasks" && $request->task_user != "") {
            $user->moveTasks($request->task_user);
        }
        if ($request->leads == "move_all_leads" && $request->lead_user != "") {
            $user->moveLeads($request->lead_user);
        }
        if ($request->clients == "move_all_clients" && $request->client_user != "") {
            $user->moveClients($request->client_user);
        }

        try {
            $user->delete();
            Session()->flash('flash_message', __('User successfully deleted'));
        } catch (\Illuminate\Database\QueryException $e) {
            Session()->flash('flash_message_warning', __('User can NOT have, leads, clients, or tasks assigned when deleted'));
        }

        return redirect()->route('users.index');
    }

    /**
    * @param $external_id
    * @return mixed
    */
    public function findByExternalId($external_id)
    {
        return User::whereExternalId($external_id)->firstOrFail();
    }
    /**
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    private function allRoles()
    {
        if (auth()->user()->roles->first()->name == Role::OWNER_ROLE) {
            return Role::all('display_name', 'id', 'name', 'external_id')->sortBy('display_name');
        }

        return Role::all('display_name', 'id', 'name', 'external_id')->filter(function ($value, $key) {
            return $value->name != "owner";
        })->sortBy('display_name');
    }
}
