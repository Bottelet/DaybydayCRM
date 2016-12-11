<?php
namespace App\Repositories\Department;

interface DepartmentRepositoryContract
{
    public function getAllDepartments();
    
    public function listAllDepartments();

    public function create($requestData);

    public function destroy($id);
}
