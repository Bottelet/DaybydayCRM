<?php
namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;
use App\Tasks;
use App\Settings;
use Session;
use Illuminate\Http\Request;
use Gate;
use Datatables;
use Carbon;
use PHPZen\LaravelRbac\Traits\Rbac;
use App\Role;
use Auth;
use Illuminate\Support\Facades\Input;
use App\Client;
use App\Department;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Requests\User\StoreUserRequest;

class UsersController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return view('users.index');
    }

    public function anyData()
    {

        $canUpdateUser = Auth::user()->canDo('update.user');
        $users = User::select(['id', 'name', 'email', 'work_number']);
        return Datatables::of($users)
        ->addColumn('namelink', function ($users) {
                return '<a href="users/'.$users->id.'" ">'.$users->name.'</a>';
        })

        ->addColumn('action', function ($users) {
                return '<a href="users/'.$users->id.'/edit" class="btn btn-success"> Edit</a>';
        })
        ->make(true);
    }

    public function taskData($id)
    {
        
        $tasks = Tasks::select(
            ['id', 'title', 'created_at', 'deadline', 'fk_user_id_assign']
        )
        ->where('fk_user_id_assign', $id)->where('status', 1);
        return Datatables::of($tasks)
        ->addColumn('titlelink', function ($tasks) {
                return '<a href="' . route('tasks.show', $tasks->id). '">'.$tasks->title.'</a>';
        })
        ->editColumn('created_at', function ($tasks) {
                return $tasks->created_at ? with(new Carbon($tasks->created_at))
                ->format('d/m/Y') : '';
        })
        ->editColumn('deadline', function ($tasks) {
                return $tasks->created_at ? with(new Carbon($tasks->created_at))
                ->format('d/m/Y') : '';
        })
        ->make(true);
    }

    public function closedtaskData($id)
    {
        
        $tasks = Tasks::select(
            ['id', 'title', 'created_at', 'deadline', 'fk_user_id_assign']
        )
        ->where('fk_user_id_assign', $id)->where('status', 2);
        return Datatables::of($tasks)
        ->addColumn('titlelink', function ($tasks) {
                return '<a href="' . route('tasks.show', $tasks->id). '">'.$tasks->title.'</a>';
        })
        ->editColumn('created_at', function ($tasks) {
                return $tasks->created_at ? with(new Carbon($tasks->created_at))
                ->format('d/m/Y') : '';
        })
        ->editColumn('deadline', function ($tasks) {
                return $tasks->created_at ? with(new Carbon($tasks->created_at))
                ->format('d/m/Y') : '';
        })
        ->make(true);
    }

    public function clientData($id)
    {
        
        $clients = Client::select(['id', 'name', 'company_name', 'primary_number', 'email'])->where('fk_user_id', $id);
        return Datatables::of($clients)
        ->addColumn('clientlink', function ($clients) {
                return '<a href="' . route('clients.show', $clients->id). '">'.$clients->name.'</a>';
        })
        ->editColumn('created_at', function ($clients) {
                return $clients->created_at ? with(new Carbon($clients->created_at))
                ->format('d/m/Y') : '';
        })
        ->editColumn('deadline', function ($clients) {
                return $clients->created_at ? with(new Carbon($clients->created_at))
                ->format('d/m/Y') : '';
        })
        ->make(true);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $canCreateUser = Auth::user()->canDo('user.create');
        if (!$canCreateUser) {
            Session::flash('flash_message_warning', 'Not allowed to create user!');
            return redirect()->route('users.index');
        }
        $roles = Role::lists('name', 'id');

        $departments = Department::lists('name', 'id');
        
        return view('users.create')->withRoles($roles)->withDepartments($departments);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(StoreUserRequest $userRequest)
    {
        $settings = Settings::all();

        $password =  bcrypt($userRequest->password);
        $role = $userRequest->roles;
        $department = $userRequest->departments;
        //dd($department);
        if ($userRequest->hasFile('image_path')) {
            if (!is_dir(public_path(). '/images/'. $companyname)) {
                      mkdir(public_path(). '/images/'. $companyname, 0777, true);
            }
            $settings = Settings::findOrFail(1);
            $companyname = $settings->company;
            $file =  $userRequest->file('image_path');

            $destinationPath = public_path(). '/images/'. $companyname;
            $filename = str_random(8) . '_' . $file->getClientOriginalName() ;

            $file->move($destinationPath, $filename);
            
            $input =  array_replace($userRequest->all(), ['image_path'=>"$filename", 'password'=>"$password"]);
        } else {
          //$input = $userRequest->all();
            $input =  array_replace($userRequest->all(), ['password'=>"$password"]);
        }

        $user = User::create($input);
        $user->roles()->attach($role);
        $user->department()->attach($department);
        $user->save();

        //dd($usersAmount);
        Session::flash('flash_message', 'User successfully added!'); //Snippet in Master.blade.php
        return redirect()->route('users.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $settings = Settings::findOrFail(1);
        $companyname = $settings->company;
        $user = User::with('tasksAssign')->whereId($id)->firstOrFail();
        return view('users.show', compact('user', 'tasks', 'clients', 'companyname'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $canUpdateUser = Auth::user()->canDo('user.update');
        if (!$canUpdateUser) {
            Session::flash('flash_message_warning', 'Not allowed to update user!');
            return redirect()->route('users.index');
        }

        $roles = Role::lists('name', 'id');
        $departments = Department::lists('name', 'id');
        $user = User::findorFail($id);
        
        return view('users.edit')
        ->withUser($user)->withRoles($roles)->withDepartment($departments);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id, UpdateUserRequest $request)
    {

        $user = User::findorFail($id);
        $password = bcrypt($request->password);
        $role = $request->roles;
        $department = $request->department;

        if ($request->hasFile('image_path')) {
            $settings = Settings::findOrFail(1);
            $companyname = $settings->company;
            $file =  $request->file('image_path');

            $destinationPath = public_path(). '\\images\\'. $companyname;
            $filename = str_random(8) . '_' . $file->getClientOriginalName() ;

            $file->move($destinationPath, $filename);
            if ($request->password == "") {
                $input =  array_replace($request->except('password'), ['image_path'=>"$filename"]);
            } else {
                $input =  array_replace($request->all(), ['image_path'=>"$filename", 'password'=>"$password"]);
            }
        } else {
            if ($request->password == "") {
                $input =  array_replace($request->except('password'));
            } else {
                $input =  array_replace($request->all(), ['password'=>"$password"]);
            }
        }
        
      

        $user->fill($input)->save();
        $user->roles()->sync([$role]);
        $user->department()->sync([$department]);

     
        Session::flash('flash_message', 'User successfully updated!');
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $user = User::findorFail($id);
        $user->delete();
        return redirect()->route('users.index');
    }
}
