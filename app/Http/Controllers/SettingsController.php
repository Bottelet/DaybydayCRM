<?php
namespace App\Http\Controllers;

use Auth;
use Session;
use App\Models\User;
use App\Models\Role;
use App\Http\Requests;
use App\Models\Settings;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use App\Repositories\Setting\SettingRepositoryContract;
use App\Repositories\Role\RoleRepositoryContract;
use App\Http\Requests\Setting\UpdateSettingOverallRequest;

class SettingsController extends Controller
{
    protected $settings;
    protected $roles;

    public function __construct(
        SettingRepositoryContract $settings,
        RoleRepositoryContract $roles
    ) {
        $this->settings = $settings;
        $this->roles = $roles;
        $this->middleware('user.is.admin', ['only' => ['index']]);
    }
    public function index()
    {
        return view('settings.index')
        ->withSettings($this->settings->getSetting())
        ->withPermission($this->roles->allPermissions())
        ->withRoles($this->roles->allRoles());
    }

    public function updateOverall(UpdateSettingOverallRequest $request)
    {
        $this->settings->updateOverall($request);
        Session::flash('flash_message', 'Overall settings successfully updated');
        return redirect()->back();
    }

    public function permissionsUpdate(Request $request)
    {
        $this->roles->permissionsUpdate($request);
        Session::flash('flash_message', 'Role is updated');
        return redirect()->back();
    }
}
