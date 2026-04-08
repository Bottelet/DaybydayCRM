<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Uses syncWithoutDetaching to prevent duplicate key errors.
     *
     * @return void
     */
    public function run()
    {
        $ownerRole = Role::where('name', Role::OWNER_ROLE)->first();
        $allPermissions = Permission::all()->pluck('id')->toArray();
        
        // Use syncWithoutDetaching to prevent duplicate key errors
        $ownerRole->perms()->syncWithoutDetaching($allPermissions);

        $adminRole = Role::where('name', Role::ADMIN_ROLE)->first();
        
        // Use syncWithoutDetaching to prevent duplicate key errors
        $adminRole->perms()->syncWithoutDetaching($allPermissions);
    }
}
