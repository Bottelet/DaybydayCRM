<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Permissions;
use App\Role;
use App\Settings;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Session;
use Auth;


class SettingsController extends Controller
{
    public function index()
    {

    	$permission = Permissions::all();
        $roles = Role::with('permissions')->get();
    	$settings = Settings::findOrFail(1);
      

    	
    	/*dd(return view('settings.index')
    	->withSettings($settings)
    	->withPermissionRole($PermissionRole));*/
        
    	return view('settings.index')
    	->withSettings($settings)
    	->withPermission($permission)
    	->withRoles($roles);

    }

    public function stripe()
    {
    	
    	$token = Input::get('stripeToken');
    	$user = User::find(1);
    	
		$user->newSubscription('main', 'Monthly')->create($token,
			[
		    'email' => auth::user()->email
			]);
		return redirect()->back();
    }

    public function updateoverall(Request $request)
    {

    	$setting = Settings::findOrFail(1);

    	$this->validate($request, [
    		'task_complete_allowed' => 'required',
    		'task_assign_allowed'   => 'required',
    		'lead_complete_allowed' => 'required',
    		'lead_assign_allowed'   => 'required'
		]);


		$input = $request->all();

		$setting->fill($input)->save();

		Session::flash('flash_message', 'Overall settings successfully updated!');
        return redirect()->back();
    }

   public function permissionsUpdate(Request $request)
    {
        $allowed_permissions = []; 
        foreach($request->input('permissions') as $permissionId => $permission) {
        if ($permission === '1') {
        $allowed_permissions[] = (int)$permissionId;
        }
        }
       
        $role = Role::find($request->input('role_id'));

        $role->permissions()->sync($allowed_permissions);
        $role->save();
    	 /*$allowed_permissions = []; 
        
        foreach($request->input('permissions') as $permission => $allowed) {
        if ($allowed == 1) {
        $allowed_permissions[] = $permission;
        }
        
	    }
       $role = Role::find($request->input('role_id'));
       var_dump($allowed_permissions);
	   $role->permissions()->sync($allowed_permissions);
	    $role->save();*/
    return redirect()->back();

    }

}
