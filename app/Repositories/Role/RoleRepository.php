<?php

namespace App\Repositories\Role;

use App\Models\Role;
use App\Models\Permissions;

/**
 * Class RoleRepository.
 */
class RoleRepository implements RoleRepositoryContract
{
    /**
     * @return mixed
     */
    public function listAllRoles()
    {
        return Role::pluck('name', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function allPermissions()
    {
        return Permissions::all();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function allRoles()
    {
        return Role::all();
    }

    /**
     * @param $requestData
     */
    public function permissionsUpdate($requestData)
    {
        $allowed_permissions = [];

        if (null != $requestData->input('permissions')) {
            foreach ($requestData->input('permissions')
                     as $permissionId => $permission) {
                if ('1' === $permission) {
                    $allowed_permissions[] = (int) $permissionId;
                }
            }
        } else {
            $allowed_permissions = [];
        }

        $role = Role::find($requestData->input('role_id'));

        $role->permissions()->sync($allowed_permissions);
        $role->save();
    }

    /**
     * @param $requestData
     */
    public function create($requestData)
    {
        $roleName        = $requestData->name;
        $roleDescription = $requestData->description;
        Role::create([
            'name'         => strtolower($roleName),
            'display_name' => ucfirst($roleName),
            'description'  => $roleDescription,
        ]);
    }

    /**
     * @param $id
     */
    public function destroy($id)
    {
        $role = Role::findorFail($id);
        if (1 !== $role->id) {
            $role->delete();
        } else {
            Session()->flash('flash_message_warning', 'Can not delete Administrator role');
        }
    }
}
