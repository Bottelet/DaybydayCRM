<?php
namespace App\Http\Controllers;

use App\Models\Role;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Repositories\Role\RoleRepositoryContract;

class RolesController extends Controller
{

    protected $roles;

    public function __construct(RoleRepositoryContract $roles)
    {
        $this->roles = $roles;
        $this->middleware('user.is.admin', ['only' => ['index', 'create', 'destroy']]);
    }
    public function index()
    {
        return view('roles.index')
        ->withRoles($this->roles->allRoles());
    }
    public function create()
    {
        return view('roles.create');
    }
    public function store(StoreRoleRequest $request)
    {
        $this->roles->create($request);
        Session()->flash('flash_message', 'Role created');
        return redirect()->back();
    }
    public function destroy($id)
    {
        dd("test");
        $this->roles->destroy($id);
        Session()->flash('flash_message', 'Role deleted');
        return redirect()->route('roles.index');
    }
}
