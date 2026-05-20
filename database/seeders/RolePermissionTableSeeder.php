<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Uses syncWithoutDetaching to prevent duplicate key errors on re-seed.
     *
     * @return void
     */
    public function run()
    {
        $allPermissions = Permission::all()->pluck('id')->toArray();

        foreach ([Role::OWNER_ROLE, Role::ADMIN_ROLE] as $roleName) {
            $role = Role::where('name', $roleName)->first();

            if ( ! $role) {
                $this->command->warn("RolePermissionTableSeeder: role '{$roleName}' not found, skipping.");
                continue;
            }

            $role->perms()->syncWithoutDetaching($allPermissions);
        }
    }
}
