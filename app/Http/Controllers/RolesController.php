<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Role;
use Session;
use App\Http\Requests\Role\StoreRoleRequest;

class RolesController extends Controller
{
    public function index()
    {
        $roles = Role::all();
        return view('roles.index')->withRoles($roles);
    }
    public function create()
    {
        return view('roles.create');
    }
    public function store(StoreRoleRequest $request)
    {
        $roleName = $request->name;
        $roleDescription = $request->description;
        Role::create([
        'slug' => $roleName,
             'description' => $roleDescription
             ]);
         Session::flash('flash_message', 'Role created');
        return redirect()->back();
    }
    public function destroy($id)
    {
        $role = Role::findorFail($id);
        if ($role->id !== 1) {
            $role->delete();
            return redirect()->route('roles.index');
        } else {
            Session::flash('flash_message_warning', 'Can not deleteAdministrator role');
            return redirect()->route('roles.index');
        }
    }
}
