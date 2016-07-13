<?php

use Illuminate\Database\Seeder;
use App\Permissions;
class seed_permissions_table extends Seeder
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
		$createUser->name = 'Create user';
		$createUser->slug = 'user.create';
		$createUser->description = 'Permission to create user';
		$createUser->save();

		$updateUser = new Permissions;
		$updateUser->name = 'Update user';
		$updateUser->slug = 'user.update';
		$updateUser->description = 'Permission to update user';
		$updateUser->save();

		$deleteUser = new Permissions;
		$deleteUser->name = 'Delete user';
		$deleteUser->slug = 'user.delete';
		$deleteUser->description = 'Permission to update delete';
		$deleteUser->save();


		 /**
    	 * Client Permissions
    	 */
    	
		$createClient = new Permissions;
		$createClient->name = 'Create client';
		$createClient->slug = 'client.create';
		$createClient->description = 'Permission to create client';
		$createClient->save();

		$updateClient = new Permissions;
		$updateClient->name = 'Update client';
		$updateClient->slug = 'client.update';
		$updateClient->description = 'Permission to update client';
		$updateClient->save();

		$deleteClient = new Permissions;
		$deleteClient->name = 'Delete client';
		$deleteClient->slug = 'client.delete';
		$deleteClient->description = 'Permission to delete client';
		$deleteClient->save();

		 /**
    	 * Tasks Permissions
    	 */
    	
		$createTask = new Permissions;
		$createTask->name = 'Create task';
		$createTask->slug = 'task.create';
		$createTask->description = 'Permission to create task';
		$createTask->save();

		$updateTask = new Permissions;
		$updateTask->name = 'Update task';
		$updateTask->slug = 'task.update';
		$updateTask->description = 'Permission to update task';
		$updateTask->save();

	     /**
    	 * Leads Permissions
    	 */
    	
    	$createLead = new Permissions;
		$createLead->name = 'Create lead';
		$createLead->slug = 'lead.create';
		$createLead->description = 'Permission to create lead';
		$createLead->save();

    	$updateLead = new Permissions;
		$updateLead->name = 'Update lead';
		$updateLead->slug = 'lead.update';
		$updateLead->description = 'Permission to update lead';
		$updateLead->save();
    }

}
