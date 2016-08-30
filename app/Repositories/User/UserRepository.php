<?php
namespace App\Repositories\User;

use App\Models\User;
use App\Models\Tasks;
use App\Models\Settings;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Gate;
use Datatables;
use Carbon;
use PHPZen\LaravelRbac\Traits\Rbac;
use App\Models\Role;
use Auth;
use Illuminate\Support\Facades\Input;
use App\Models\Client;
use App\Models\Department;
use DB;

class UserRepository implements UserRepositoryContract
{

    public function find($id)
    {
        return User::findOrFail($id);
    }

    public function getAllUsers()
    {
        return User::all();
    }

    public function getAllUsersWithDepartments()
    {
        return  User::select(array('users.name', 'users.id',
                DB::raw('CONCAT(users.name, " (", departments.name, ")") AS full_name')))
        ->join('department_user', 'users.id', '=', 'department_user.user_id')
        ->join('departments', 'department_user.department_id', '=', 'departments.id')
        ->pluck('full_name', 'id');
    }



    public function create($requestData)
    {
        $settings = Settings::all();

        $password =  bcrypt($requestData->password);
        $role = $requestData->roles;
        $department = $requestData->departments;
        if ($requestData->hasFile('image_path')) {
            if (!is_dir(public_path(). '/images/'. $companyname)) {
                mkdir(public_path(). '/images/'. $companyname, 0777, true);
            }
            $settings = Settings::findOrFail(1);
            $companyname = $settings->company;
            $file =  $requestData->file('image_path');

            $destinationPath = public_path(). '/images/'. $companyname;
            $filename = str_random(8) . '_' . $file->getClientOriginalName() ;

            $file->move($destinationPath, $filename);
            
            $input =  array_replace($requestData->all(), ['image_path'=>"$filename", 'password'=>"$password"]);
        } else {
            $input =  array_replace($requestData->all(), ['password'=>"$password"]);
        }

        $user = User::create($input);
        $user->roles()->attach($role);
        $user->department()->attach($department);
        $user->save();

        Session::flash('flash_message', 'User successfully added!'); //Snippet in Master.blade.php
        return $user;
    }

    public function update($id, $requestData)
    {
        $user = User::findorFail($id);
        $password = bcrypt($requestData->password);
        $role = $requestData->roles;
        $department = $requestData->department;

        if ($requestData->hasFile('image_path')) {
            $settings = Settings::findOrFail(1);
            $companyname = $settings->company;
            $file =  $requestData->file('image_path');

            $destinationPath = public_path(). '\\images\\'. $companyname;
            $filename = str_random(8) . '_' . $file->getClientOriginalName() ;

            $file->move($destinationPath, $filename);
            if ($requestData->password == "") {
                $input =  array_replace($requestData->except('password'), ['image_path'=>"$filename"]);
            } else {
                $input =  array_replace($requestData->all(), ['image_path'=>"$filename", 'password'=>"$password"]);
            }
        } else {
            if ($requestData->password == "") {
                $input =  array_replace($requestData->except('password'));
            } else {
                $input =  array_replace($requestData->all(), ['password'=>"$password"]);
            }
        }

        $user->fill($input)->save();
        $user->roles()->sync([$role]);
        $user->department()->sync([$department]);

        Session::flash('flash_message', 'User successfully updated!');

        return $user;
    }

    public function destroy($id)
    {
        if ($id == 1) {
            return Session()->flash('flash_message_warning', 'Not allowed to delete super admin');
        }
        try {
            $user = User::findorFail($id);
            $user->delete();
            Session()->flash('flash_message', 'User successfully deleted');
        } catch (\Illuminate\Database\QueryException $e) {
            Session()->flash('flash_message_warning', 'User can NOT have, leads, clients, or tasks assigned when deleted');
        }
    }
}
