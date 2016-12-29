<?php
namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Repositories\Role\RoleRepositoryContract;

class RolesController extends Controller
{

    protected $roles;

    /**
     * RolesController constructor.
     * @param RoleRepositoryContract $roles
     */
    public function __construct(RoleRepositoryContract $roles)
    {
        $this->roles = $roles;
        $this->middleware('user.is.admin', ['only' => ['index', 'create', 'destroy']]);
    }

    /**
     * @return mixed
     */
    public function index()
    {
        return view('roles.index')
            ->withRoles($this->roles->allRoles());
    }

    /**
     * @return mixed
     */
    public function create()
    {
        return view('roles.create');
    }

    /**
     * @param StoreRoleRequest $request
     * @return mixed
     */
    public function store(StoreRoleRequest $request)
    {
        $this->roles->create($request);
        Session()->flash('flash_message', 'Role created');
        return redirect()->back();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function destroy($id)
    {
        $this->roles->destroy($id);
        Session()->flash('flash_message', 'Role deleted');
        return redirect()->route('roles.index');
    }
}
