<?php

namespace App\Http\Controllers;

use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Models\Integration;
use App\Models\Permission;
use App\Models\Role;
use App\Services\Role\RoleService;
use Illuminate\Support\Facades\Session;
use Throwable;
use Yajra\Datatables\Datatables;

class RolesController extends Controller
{
    /**
     * RolesController constructor.
     */
    public function __construct(private RoleService $roleService)
    {
        $this->middleware('user.is.admin', ['only' => ['index', 'create', 'destroy', 'show', 'update']]);
        $this->middleware('is.demo', ['except' => ['index', 'create', 'show', 'indexData']]);
    }

    /**
     * Make json response for datatables.
     *
     * @return mixed
     */
    public function indexData()
    {
        $roles = Role::query()->select(['id', 'name', 'external_id', 'display_name']);

        return Datatables::of($roles)
            ->addColumn('namelink', function ($roles) {
                if ($roles->name == Role::OWNER_ROLE) {
                    return '<a href="' . route('roles.show', $roles->external_id) . '">' . htmlspecialchars($roles->display_name, ENT_QUOTES, 'UTF-8') . '</a>' . '<br>' . __('Extra: Owner is able to do the same as an administrator but also controls billing');
                }
                if ($roles->name == Role::ADMIN_ROLE) {
                    return '<a href="' . route('roles.show', $roles->external_id) . '">' . htmlspecialchars($roles->display_name, ENT_QUOTES, 'UTF-8') . '</a>' . '<br>' . __('Extra: Administrator is able to update and create departments, integrations, and settings');
                }

                return '<a href="' . route('roles.show', $roles->external_id) . '">' . htmlspecialchars($roles->display_name, ENT_QUOTES, 'UTF-8') . '</a>';
            })
            ->editColumn('permissions', function ($roles) {
                return $roles->permissions->map(function ($permission) {
                    return $permission->display_name;
                })->implode('<br>');
            })
            ->addColumn('view', '
                <a href="{{ route(\'roles.show\', $external_id) }}" class="btn btn-link" >' . __('View') . '</a>')

            ->addColumn('delete', function ($roles) {
                if ($roles->canBeDeleted()) {
                    return '
                <form action="' . route('roles.destroy', $roles->external_id) . '" method="POST">
            <input type="hidden" name="_method" value="DELETE">
            <input type="submit" name="submit" value="' . __('Delete') . '" class="btn btn-link" onClick="return confirm(\'Are you sure?\')"">
            <input type="hidden" name="_token" value="' . csrf_token() . '">
            </form>';
                }
            })
            ->rawColumns(['namelink', 'view', 'delete', 'permissions'])
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

        if ( ! Integration::whereApiType('file')->first()) {
            unset($permissions_grouping['document']);
        }

        return view('roles.show')
            ->withRole(Role::whereExternalId($external_id)->first())
            ->with('permissions_grouping', $permissions_grouping);
    }

    /**
     * @return mixed
     */
    public function store(StoreRoleRequest $request)
    {
        try {
            $this->roleService->create($request->validated());
        } catch (Throwable $exception) {
            report($exception);

            return $this->failureResponse(
                $request,
                __('Role could not be created. Please try again.'),
                'role'
            );
        }

        session()->flash('flash_message', __('Role created'));

        return view('roles.index');
    }

    /**
     * @return mixed
     */
    public function destroy($external_id)
    {
        $role = Role::query()->where('external_id', $external_id)->firstOrFail();

        if ( ! $this->roleService->destroy($role)) {
            Session::flash('flash_message_warning', __("Can't delete role with users, please remove users"));

            return redirect()->route('roles.index');
        }

        Session::flash('flash_message', __('Role deleted'));

        return redirect()->route('roles.index');
    }

    /**
     * @return mixed
     */
    public function update(UpdateRoleRequest $request, $external_id)
    {
        $role = Role::whereExternalId($external_id)->firstOrFail();
        $this->roleService->syncPermissions($role, $request->validated('permissions'));
        Session::flash('flash_message', __('Role is updated'));

        return redirect()->back();
    }
}
