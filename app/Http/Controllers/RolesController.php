<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Role;
use Session;
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
    public function store(Request $request)
    {
    	$this->validate($request, [
    		'name' => 'required',
    		'description' => 'required'
    	]);

    	
    	$roleName = $request->name;
    	$roleDescription = $request->description;
    	Role::create([
    	'slug' => $roleName, 
    		 'description' => $roleDescription
    		 ]);
    	
    }
    public function destroy($id)
    {
    	$role = Role::findorFail($id);
    	if ($role->id !== 1) {
    		$role->delete();
    		return redirect()->route('roles.index');
    	}else{
    		Session::flash('flash_message_warning', 'Can not deleteAdministrator role');
    		return redirect()->route('roles.index');
    	}
    	
    	
    }

}
