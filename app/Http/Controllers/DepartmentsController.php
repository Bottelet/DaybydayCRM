<?php

namespace App\Http\Controllers;

use App\Http\Requests\Department\StoreDepartmentRequest;
use App\Models\Department;
use App\Services\Department\DepartmentService;
use Exception;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\Facades\DataTables;

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
        $departments = Department::query()->select(['external_id', 'name', 'description']);

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
     * @return mixed
     */
    public function store(StoreDepartmentRequest $request, DepartmentService $service)
    {
        $service->store($request->validated());
        Session::flash('flash_message', __('Successfully created new department'));

        return redirect()->route('departments.index');
    }

    /**
     * @return mixed
     */
    public function destroy($external_id, DepartmentService $service)
    {
        try {
            $service->destroy($external_id);
        } catch (Exception $e) {
            Session::flash('flash_message_warning', $e->getMessage());

            return redirect()->route('departments.index');
        }

        return redirect()->route('departments.index');
    }
}
