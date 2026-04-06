<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role = Role::where('name', Role::OWNER_ROLE)->first();
        foreach (Permission::all() as $permission) {
            PermissionRole::create([
                'role_id' => $role->id,
                'permission_id' => $permission->id,
            ]);
        }

        $role = Role::where('name', Role::ADMIN_ROLE)->first();
        foreach (Permission::all() as $permission) {
            PermissionRole::create([
                'role_id' => $role->id,
                'permission_id' => $permission->id,
            ]);
        }
    }
}
