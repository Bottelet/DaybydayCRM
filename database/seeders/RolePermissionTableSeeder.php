<?php

use Illuminate\Database\Seeder;
use App\Models\PermissionRole;

class RolePermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role = \App\Models\Role::where('name', \App\Models\Role::OWNER_ROLE)->first();
        foreach (\App\Models\Permission::all() as $permission) {
            PermissionRole::create([
               'role_id' => $role->id,
               'permission_id' => $permission->id
            ]);
        }
        
        $role = \App\Models\Role::where('name', \App\Models\Role::ADMIN_ROLE)->first();
        foreach (\App\Models\Permission::all() as $permission) {
            PermissionRole::create([
               'role_id' => $role->id,
               'permission_id' => $permission->id
            ]);
        }
    }
}
