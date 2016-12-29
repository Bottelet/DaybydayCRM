<?php
namespace App\Repositories\Department;

use App\Models\Department;

/**
 * Class DepartmentRepository
 * @package App\Repositories\Department
 */
class DepartmentRepository implements DepartmentRepositoryContract
{
    /**
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getAllDepartments()
    {
        return Department::all();
    }

    /**
     * @return mixed
     */
    public function listAllDepartments()
    {
        return Department::pluck('name', 'id');
    }

    /**
     * @param $requestData
     */
    public function create($requestData)
    {
        Department::create($requestData->all());
    }

    /**
     * @param $id
     */
    public function destroy($id)
    {
        Department::findorFail($id)->delete();
    }
}
