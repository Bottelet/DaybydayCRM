<?php

use Illuminate\Database\Seeder;
use App\Models\Permissions;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /**
         * User Permissions
         */
        
        $createUser = new Permissions;
        $createUser->display_name = 'Create user';
        $createUser->name = 'user-create';
        $createUser->description = 'Permission to create user';
        $createUser->save();

        $updateUser = new Permissions;
        $updateUser->display_name = 'Update user';
        $updateUser->name = 'user-update';
        $updateUser->description = 'Permission to update user';
        $updateUser->save();

        $deleteUser = new Permissions;
        $deleteUser->display_name = 'Delete user';
        $deleteUser->name = 'user-delete';
        $deleteUser->description = 'Permission to update delete';
        $deleteUser->save();


         /**
         * Client Permissions
         */
        
        $createClient = new Permissions;
        $createClient->display_name = 'Create client';
        $createClient->name = 'client-create';
        $createClient->description = 'Permission to create client';
        $createClient->save();

        $updateClient = new Permissions;
        $updateClient->display_name = 'Update client';
        $updateClient->name = 'client-update';
        $updateClient->description = 'Permission to update client';
        $updateClient->save();

        $deleteClient = new Permissions;
        $deleteClient->display_name = 'Delete client';
        $deleteClient->name = 'client-delete';
        $deleteClient->description = 'Permission to delete client';
        $deleteClient->save();

         /**
         * Tasks Permissions
         */
        
        $createTask = new Permissions;
        $createTask->display_name = 'Create task';
        $createTask->name = 'task-create';
        $createTask->description = 'Permission to create task';
        $createTask->save();

        $updateTask = new Permissions;
        $updateTask->display_name = 'Update task';
        $updateTask->name = 'task-update';
        $updateTask->description = 'Permission to update task';
        $updateTask->save();

         /**
         * Leads Permissions
         */
        
        $createLead = new Permissions;
        $createLead->display_name = 'Create lead';
        $createLead->name = 'lead-create';
        $createLead->description = 'Permission to create lead';
        $createLead->save();

        $updateLead = new Permissions;
        $updateLead->display_name = 'Update lead';
        $updateLead->name = 'lead-update';
        $updateLead->description = 'Permission to update lead';
        $updateLead->save();
    }
}
