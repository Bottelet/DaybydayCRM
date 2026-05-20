<?php

namespace App\Services\Role;

use App\Models\Role;

class RoleService
{
    public function create(array $validated): Role
    {
        return Role::query()->create([
            'name'         => mb_strtolower($validated['name']),
            'display_name' => ucwords($validated['name']),
            'description'  => $validated['description'],
        ]);
    }

    public function syncPermissions(Role $role, array $permissions): void
    {
        $allowedPermissions = [];

        foreach ($permissions as $permissionId => $permission) {
            if ($permission === '1') {
                $allowedPermissions[] = (int) $permissionId;
            }
        }

        $role->permissions()->sync($allowedPermissions);
    }

    public function destroy(Role $role): bool
    {
        if ($role->users()->exists()) {
            return false;
        }

        if ($role->name === Role::ADMIN_ROLE || $role->name === Role::OWNER_ROLE) {
            return false;
        }

        $role->delete();

        return true;
    }
}
