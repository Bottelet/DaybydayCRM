<?php
namespace App\Repositories\Role;

interface RoleRepositoryContract
{
    public function listAllRoles();

    public function allPermissions();

    public function allRoles();

    public function permissionsUpdate($requestData, $id);

    public function create($requestData);

    public function destroy($id);
}
