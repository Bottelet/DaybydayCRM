<?php

namespace App\Services\Department;

use App\Models\Department;
use Exception;

class DepartmentService
{
    /**
     * Store a new department.
     *
     * @param array $data The validated data
     *
     * @return Department The created department
     */
    public function store(array $data): Department
    {
        return Department::create($data);
    }

    /**
     * Delete a department by external ID.
     *
     * @param string $externalId The external ID of the department
     *
     * @throws Exception If the department has associated users
     */
    public function destroy(string $externalId): void
    {
        $department = Department::whereExternalId($externalId)->firstOrFail();

        if ($department->users()->count() > 0) {
            throw new Exception(__('Department has users associated with it'));
        }

        $department->delete();
    }
}
