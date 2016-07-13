<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Department;
use Session;

class DepartmentsController extends Controller
{
	public function index()
	{
		$departments = Department::all();
		return view('departments.index')->withDepartment($departments);
	}
    public function create()
    {
    	return view('departments.create');
    }
    public function store(Request $request)
    {
    	$this->validate($request, [
    		'name' => 'required']);

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
