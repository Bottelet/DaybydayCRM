<?php
namespace App\Repositories\Role;

use App\Models\Role;
use App\Models\Permissions;

/**
 * Class RoleRepository
 * @package App\Repositories\Role
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

        if ($requestData->input('permissions') != null) {
            foreach ($requestData->input('permissions')
                     as $permissionId => $permission) {
                if ($permission === '1') {
                    $allowed_permissions[] = (int)$permissionId;
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
        $roleName = $requestData->name;
        $roleDescription = $requestData->description;
        Role::create([
            'name' => strtolower($roleName),
            'display_name' => ucfirst($roleName),
            'description' => $roleDescription
        ]);
    }

    /**
     * @param $id
     */
    public function destroy($id)
    {
        $role = Role::findorFail($id);
        if ($role->id !== 1) {
            $role->delete();
        } else {
            Session()->flash('flash_message_warning', 'Can not delete Administrator role');
        }
    }
}
