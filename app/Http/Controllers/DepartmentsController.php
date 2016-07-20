<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Department;
use Session;
use App\Http\Requests\Department\StoreDepartmentRequest;
use App\Repositories\Department\DepartmentRepositoryContract;

class DepartmentsController extends Controller
{

    protected $departments;

    public function __construct(DepartmentRepositoryContract $departments)
    {
        $this->departments = $departments;
        $this->middleware('user.is.admin', ['only' => ['create', 'destroy']]);
    }
    public function index()
    {
        return view('departments.index')
        ->withDepartment($this->departments->getAllDepartments());
    }
    public function create()
    {
        return view('departments.create');
    }
    public function store(StoreDepartmentRequest $request)
    {
        $this->departments->create($request);
        Session::flash('flash_message', 'Successfully created New Department');
        return redirect()->route('departments.index');
    }
    public function destroy($id)
    {
        $this->departments->destroy($id);
        return redirect()->route('departments.index');
    }
}
