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
use App\Http\Requests\Setting\UpdateSettingOverallRequest;

class SettingsController extends Controller
{
    public function index()
    {

        $permission = Permissions::all();
        $roles = Role::with('permissions')->get();
        $settings = Settings::findOrFail(1);
        
        return view('settings.index')
        ->withSettings($settings)
        ->withPermission($permission)
        ->withRoles($roles);
    }

    public function stripe()
    {
        
        $token = Input::get('stripeToken');
        $user = User::find(1);
        
        $user->newSubscription('main', 'Monthly')->create(
            $token,
            [
            'email' => auth::user()->email
            ]
        );
        return redirect()->back();
    }

    public function updateOverall(UpdateSettingOverallRequest $request)
    {

        $setting = Settings::findOrFail(1);

        $input = $request->all();

        $setting->fill($input)->save();

        Session::flash('flash_message', 'Overall settings successfully updated!');
        return redirect()->back();
    }

    public function permissionsUpdate(Request $request)
    {
        $allowed_permissions = [];
        foreach ($request->input('permissions') as $permissionId => $permission) {
            if ($permission === '1') {
                $allowed_permissions[] = (int)$permissionId;
            }
        }
       
        $role = Role::find($request->input('role_id'));

        $role->permissions()->sync($allowed_permissions);
        $role->save();
        return redirect()->back();
    }
}
