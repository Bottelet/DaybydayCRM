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
        /**
         * ADMIN ROLES
         *
         */
        $createUser = new PermissionRole;
        $createUser->role_id = '1';
        $createUser->permission_id = '1';
        $createUser->timestamps = false;
        $createUser->save();

        $updateUser = new PermissionRole;
        $updateUser->role_id = '1';
        $updateUser->permission_id = '2';
        $updateUser->timestamps = false;
        $updateUser->save();

        $deleteUser = new PermissionRole;
        $deleteUser->role_id = '1';
        $deleteUser->permission_id = '3';
        $deleteUser->timestamps = false;
        $deleteUser->save();

        $createClient = new PermissionRole;
        $createClient->role_id = '1';
        $createClient->permission_id = '4';
        $createClient->timestamps = false;
        $createClient->save();

        $updateClient = new PermissionRole;
        $updateClient->role_id = '1';
        $updateClient->permission_id = '5';
        $updateClient->timestamps = false;
        $updateClient->save();

        $deleteClient = new PermissionRole;
        $deleteClient->role_id = '1';
        $deleteClient->permission_id = '6';
        $deleteClient->timestamps = false;
        $deleteClient->save();

        $createTask = new PermissionRole;
        $createTask->role_id = '1';
        $createTask->permission_id = '7';
        $createTask->timestamps = false;
        $createTask->save();

        $updateTask = new PermissionRole;
        $updateTask->role_id = '1';
        $updateTask->permission_id = '8';
        $updateTask->timestamps = false;
        $updateTask->save();

        $createLead = new PermissionRole;
        $createLead->role_id = '1';
        $createLead->permission_id = '9';
        $createLead->timestamps = false;
        $createLead->save();

        $updateLead = new PermissionRole;
        $updateLead->role_id = '1';
        $updateLead->permission_id = '10';
        $updateLead->timestamps = false;
        $updateLead->save();
    }
}
