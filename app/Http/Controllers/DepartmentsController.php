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
        $this->middleware('user.is.admin', ['only' => ['create', 'edit', 'update', 'destroy']]);
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
            ->addColumn('namelink', function ($departments) {
                return '<a href="' . route('departments.show', $departments->external_id) . '">' . e($departments->name) . '</a>';
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
            ->rawColumns(['namelink', 'delete'])
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
        $department = $service->store($request->validated());

        // Flash before the JSON early-return: the real browser create form submits
        // via AJAX (expectsJson() is true) and then does a client-side redirect, so
        // the flash must be set here to survive that navigation.
        Session::flash('flash_message', __('Successfully created new department'));

        if ($request->expectsJson()) {
            return response()->json([
                'department_external_id' => $department->external_id,
                'message'                => __('Successfully created new department'),
            ], 201);
        }

        return redirect()->route('departments.index');
    }

    /**
     * @return mixed
     */
    public function show($external_id)
    {
        return view('departments.show')
            ->withDepartment(Department::whereExternalId($external_id)->firstOrFail());
    }

    /**
     * @return mixed
     */
    public function edit($external_id)
    {
        return view('departments.edit')
            ->withDepartment(Department::whereExternalId($external_id)->firstOrFail());
    }

    /**
     * @return mixed
     */
    public function update($external_id, StoreDepartmentRequest $request, DepartmentService $service)
    {
        $department = Department::whereExternalId($external_id)->firstOrFail();
        $service->update($department, $request->validated());
        Session::flash('flash_message', __('Successfully updated department'));

        if ($request->expectsJson()) {
            return response()->json([
                'department_external_id' => $department->external_id,
                'message'                => __('Successfully updated department'),
            ], 200);
        }

        return redirect()->route('departments.show', $department->external_id);
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
