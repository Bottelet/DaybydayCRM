<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Department;
use Session;
use App\Http\Requests\Department\StoreDepartmentRequest;

class DepartmentsController extends Controller
{
    public function __construct()
    {
        $this->middleware('user.is.admin', ['only' => ['create', 'destroy']]);
    }
    public function index()
    {
        $departments = Department::all();
        return view('departments.index')->withDepartment($departments);
    }
    public function create()
    {
        return view('departments.create');
    }
    public function store(StoreDepartmentRequest $request)
    {
        $input = $request->all();
        $department = Department::create($input);
        Session::flash('flash_message', 'Successfully created New Department!');
        return view('departments.create');
    }
    public function destroy($id)
    {
        $department = Department::findorFail($id);
        
        $department->delete();
        
        return redirect()->route('departments.index');
    }
}
