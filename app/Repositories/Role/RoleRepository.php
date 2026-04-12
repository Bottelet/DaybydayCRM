<?php

namespace App\Repositories\Role;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class RoleRepository
 */
class RoleRepository implements RoleRepositoryContract
{
    /**
     * @return mixed
     */
    public function listAllRoles()
    {
        return $this->allRoles()->pluck('display_name', 'id');
    }

    /**
     * @return Collection|static[]
     */
    public function allPermissions()
    {
        return Permission::all();
    }

    /**
     * @return Collection|static[]
     */
    public function allRoles()
    {
        // Get rid of owner as the should only be one.
        return Role::all(['display_name', 'id', 'name', 'external_id'])->filter(function ($value, $key) {
            return $value->name != 'owner';
        });
    }

    public function permissionsUpdate($requestData, $external_id)
    {
        $allowed_permissions = [];

        if ($requestData->input('permissions') != null) {
            foreach ($requestData->input('permissions') as $permissionId => $permission) {
                if ($permission === '1') {
                    $allowed_permissions[] = (int) $permissionId;
                }
            }
        } else {
            $allowed_permissions = [];
        }

        $role = Role::whereExternalId($external_id)->first();

        $role->permissions()->sync($allowed_permissions);
        $role->save();
    }

    public function create($requestData)
    {
        $roleName = $requestData->name;
        $roleDescription = $requestData->description;
        Role::create([
            'name' => strtolower($roleName),
            'display_name' => ucfirst($roleName),
            'description' => $roleDescription,
        ]);
    }

    public function destroy($id)
    {
        $role = Role::findorFail($id);
        if ($role->name !== 'administrator' || $role->name !== 'owner') {
            $role->delete();
        } else {
            session()->flash('flash_message_warning', 'Can not delete Administrator role');
        }
    }
}
