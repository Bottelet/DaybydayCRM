<?php
namespace App\Http\Controllers;

use Ramsey\Uuid\Uuid;
use Session;
use App\Http\Requests;
use App\Models\Department;
use App\Http\Requests\Department\StoreDepartmentRequest;
use Datatables;

class DepartmentsController extends Controller
{

    public function __construct()
    {
        $this->middleware('user.is.admin', ['only' => ['create', 'destroy']]);
        $this->middleware('is.demo', ['only' => ['destroy']]);
    }

    /**
     * @return mixed
     */
    public function index()
    {
        return view('departments.index')
            ->withDepartment(Department::all());
    }

    /**
     * @return mixed
     */
    public function indexData()
    {
        $departments = Department::select(['external_id', 'name', 'description']);
        return Datatables::of($departments)
            ->editColumn('name', function ($departments) {
                return $departments->name;
            })
            ->editColumn('description', function ($departments) {
                return $departments->description;
            })
            ->addColumn('delete', '
                <form action="{{ route(\'departments.destroy\', $external_id) }}" method="POST">
            <input type="hidden" name="_method" value="DELETE">
            {{csrf_field()}}
            <input type="submit" name="submit" value="' . __('Delete') . '" class="btn btn-link" onClick="return confirm(\'Are you sure?\')"">
            </form>')
            ->rawColumns(['delete'])
            ->make(true);
    }

    /**
     * @return mixed
     */
    public function create()
    {
        return view('departments.create');
    }

    /**
     * @param StoreDepartmentRequest $request
     * @return mixed
     */
    public function store(StoreDepartmentRequest $request)
    {
        Department::create([
            'external_id' => Uuid::uuid4(),
            'name' => $request->name,
            'description' => $request->description
        ]);
        Session::flash('flash_message', __('Successfully created new department'));
        return redirect()->route('departments.index');
    }

    /**
     * @param $external_id
     * @return mixed
     */
    public function destroy($external_id)
    {
        $department = Department::whereExternalId($external_id)->first();

        if (!$department->users->isEmpty()) {
            Session::flash('flash_message_warning', __("Can't delete department with users, please remove users"));
            return redirect()->route('departments.index');
        }
        $department->delete();
        return redirect()->route('departments.index');
    }
}
