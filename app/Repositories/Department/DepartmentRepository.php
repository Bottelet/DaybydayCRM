<?php
namespace App\Repositories\Department;

use App\Models\Department;

class DepartmentRepository implements DepartmentRepositoryContract
{

    public function getAllDepartments()
    {
        return Department::all();
    }

    public function listAllDepartments()
    {
        return Department::lists('name', 'id');
    }

    public function create($requestData)
    {
        Department::create($requestData->all());
    }

    public function destroy($id)
    {
        Department::findorFail($id)->delete();
    }
}
