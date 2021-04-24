<?php
namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use App\Models\Integration;
use App\Http\Requests\Role\StoreRoleRequest;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;
use Session;
use Yajra\Datatables\Datatables;

class RolesController extends Controller
{
    /**
     * RolesController constructor.
     */
    public function __construct()
    {
        $this->middleware('user.is.admin', ['only' => ['index', 'create', 'destroy', 'show', 'update']]);
        $this->middleware('is.demo', ['except' => ['index', 'create', 'show', 'indexData']]);
    }

    /**
     * Make json respnse for datatables
     * @return mixed
     */
    public function indexData()
    {
        $roles = Role::select(['id', 'name', 'external_id', 'display_name']);
        return Datatables::of($roles)
            ->addColumn('namelink', function ($roles) {
                if ($roles->name == Role::OWNER_ROLE) {
                    return '<a href="'.route('roles.show', $roles->external_id).'">'.htmlspecialchars($roles->display_name, ENT_QUOTES, 'UTF-8').'</a>' . '<br>' . __('Extra: Owner is able to do the same as an administrator but also controls billing');
                }
                if ($roles->name == Role::ADMIN_ROLE) {
                    return '<a href="'.route('roles.show', $roles->external_id).'">'.htmlspecialchars($roles->display_name, ENT_QUOTES, 'UTF-8').'</a>' . '<br>' . __('Extra: Administrator is able to update and create departments, integrations, and settings');
                }
                return '<a href="'.route('roles.show', $roles->external_id).'">'.htmlspecialchars($roles->display_name, ENT_QUOTES, 'UTF-8').'</a>';
            })
            ->editColumn('permissions', function ($roles) {
                return $roles->permissions->map(function ($permission) {
                    return $permission->display_name;
                })->implode("<br>");
            })
            ->addColumn('view', '
                <a href="{{ route(\'roles.show\', $external_id) }}" class="btn btn-link" >'  . __('View') . '</a>')

            ->addColumn('delete', function ($roles) {
                if ($roles->canBeDeleted()) {
                    return '
                <form action="'. route('roles.destroy', $roles->external_id) .'" method="POST">
            <input type="hidden" name="_method" value="DELETE">
            <input type="submit" name="submit" value="' . __('Delete') . '" class="btn btn-link" onClick="return confirm(\'Are you sure?\')"">
            <input type="hidden" name="_token" value="' . csrf_token(). '">
            </form>';
                }
            })
            ->rawColumns(['namelink','view','delete', 'permissions'])
            ->make(true);
    }

    /**
     * @return mixed
     */
    public function index()
    {
        return view('roles.index');
    }

    /**
     * @return mixed
     */
    public function create()
    {
        return view('roles.create');
    }

    /**
     * @return mixed
     */
    public function show($external_id)
    {
        $permissions_grouping = Permission::all()->groupBy('grouping');

        if (!Integration::whereApiType('file')->first()) {
            unset($permissions_grouping['document']);
        }
        
        return view('roles.show')
        ->withRole(Role::whereExternalId($external_id)->first())
        ->with('permissions_grouping', $permissions_grouping);
    }

    /**
     * @param StoreRoleRequest $request
     * @return mixed
     */
    public function store(StoreRoleRequest $request)
    {
        $roleName = $request->name;
        $roleDescription = $request->description;
        Role::create([
            'external_id' => Uuid::uuid4()->toString(),
            'name' => strtolower($roleName),
            'display_name' => ucfirst($roleName),
            'description' => $roleDescription
        ]);
        Session()->flash('flash_message', __('Role created'));
        return view('roles.index');
    }

    /**
     * @param $external_id
     * @return mixed
     */
    public function destroy($external_id)
    {
        $role = Role::where('external_id', $external_id)->first();
        if (!$role->users->isEmpty()) {
            Session::flash('flash_message_warning', __("Can't delete role with users, please remove users"));
            return redirect()->route('roles.index');
        }
        if ($role->name !== Role::ADMIN_ROLE && $role->name !== Role::OWNER_ROLE) {
            $role->delete();
        } else {
            Session()->flash('flash_message_warning', __('Can not delete role'));
            return redirect()->route('roles.index');
        }
        Session()->flash('flash_message', __('Role deleted'));
        return redirect()->route('roles.index');
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function update(Request $request, $external_id)
    {
        $allowed_permissions = [];

        if ($request->input('permissions') != null) {
            foreach ($request->input('permissions')
                     as $permissionId => $permission) {
                if ($permission === '1') {
                    $allowed_permissions[] = (int)$permissionId;
                }
            }
        } else {
            $allowed_permissions = [];
        }

        $role = Role::whereExternalId($external_id)->first();

        $role->permissions()->sync($allowed_permissions);
        $role->save();
        Session::flash('flash_message', __('Role is updated'));
        return redirect()->back();
    }
}
