<?php

use Illuminate\Database\Seeder;
use App\Models\Permission;

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
         * User Permission
         */
        
        $createUser = new Permission;
        $createUser->display_name = 'Create user';
        $createUser->name = 'user-create';
        $createUser->description = 'Be able to create a new user';
        $createUser->grouping = 'user';
        $createUser->save();

        $updateUser = new Permission;
        $updateUser->display_name = 'Update user';
        $updateUser->name = 'user-update';
        $updateUser->description = "Be able to update a user's information";
        $updateUser->grouping = 'user';
        $updateUser->save();

        $deleteUser = new Permission;
        $deleteUser->display_name = 'Delete user';
        $deleteUser->name = 'user-delete';
        $deleteUser->description = "Be able to delete a user";
        $deleteUser->grouping = 'user';
        $deleteUser->save();


        /**
        * Client Permission
        */
        
        $createClient = new Permission;
        $createClient->display_name = 'Create client';
        $createClient->name = 'client-create';
        $createClient->description = 'Permission to create client';
        $createClient->grouping = 'client';
        $createClient->save();

        $updateClient = new Permission;
        $updateClient->display_name = 'Update client';
        $updateClient->name = 'client-update';
        $updateClient->description = 'Permission to update client';
        $updateClient->grouping = 'client';
        $updateClient->save();

        $deleteClient = new Permission;
        $deleteClient->display_name = 'Delete client';
        $deleteClient->name = 'client-delete';
        $deleteClient->description = 'Permission to delete client';
        $deleteClient->grouping = 'client';
        $deleteClient->save();

        $deleteDocument = new Permission;
        $deleteDocument->display_name = 'Delete document';
        $deleteDocument->name = 'document-delete';
        $deleteDocument->description = 'Permission to delete a document associated with a client';
        $deleteDocument->grouping = 'document';
        $deleteDocument->save();

        $deleteDocument = new Permission;
        $deleteDocument->display_name = 'Upload document';
        $deleteDocument->name = 'document-upload';
        $deleteDocument->description = 'Be able to upload a document associated with a client';
        $deleteDocument->grouping = 'document';
        $deleteDocument->save();

        /**
        * Tasks Permission
        */
        
        $createTask = new Permission;
        $createTask->display_name = 'Create task';
        $createTask->name = 'task-create';
        $createTask->description = 'Permission to create task';
        $createTask->grouping = 'task';
        $createTask->save();

        $updateTask = new Permission;
        $updateTask->display_name = 'Update task status';
        $updateTask->name = 'task-update-status';
        $updateTask->description = 'Permission to update task status';
        $updateTask->grouping = 'task';
        $updateTask->save();

        $updateTask = new Permission;
        $updateTask->display_name = 'Change task deadline';
        $updateTask->name = 'task-update-deadline';
        $updateTask->description = 'Permission to update a tasks deadline';
        $updateTask->grouping = 'task';
        $updateTask->save();

        $assignNewUserTask = new Permission();
        $assignNewUserTask->display_name = 'Change assigned user';
        $assignNewUserTask->name = 'can-assign-new-user-to-task';
        $assignNewUserTask->description = 'Permission to change the assigned user on a task';
        $assignNewUserTask->grouping = 'task';
        $assignNewUserTask->save();

        $changeLinkedProject = new Permission();
        $changeLinkedProject->display_name = 'Changed linked project';
        $changeLinkedProject->name = 'task-update-linked-project';
        $changeLinkedProject->description = 'Be able to change the project which is linked to a task';
        $changeLinkedProject->grouping = 'task';
        $changeLinkedProject->save();

        $taskUploadFiles = new Permission();
        $taskUploadFiles->display_name = 'Upload files to task';
        $taskUploadFiles->name = 'task-upload-files';
        $taskUploadFiles->description = 'Allowed to upload files for a task';
        $taskUploadFiles->grouping = 'task';
        $taskUploadFiles->save();


        $invoicePermission = new Permission;
        $invoicePermission->display_name = 'Modify invoice lines on a invoice / task';
        $invoicePermission->name = 'modify-invoice-lines';
        $invoicePermission->description = 'Permission to create and update invoice lines on task, and invoices';
        $invoicePermission->grouping = 'invoice';
        $invoicePermission->save();

        $invoicePermission = new Permission;
        $invoicePermission->display_name = "See invoices and it's invoice lines";
        $invoicePermission->name = 'invoice-see';
        $invoicePermission->description = "Permission to see invoices on customer, and it's associated task";
        $invoicePermission->grouping = 'invoice';
        $invoicePermission->save();

        $invoicePermission = new Permission;
        $invoicePermission->display_name = "Send invoices to clients";
        $invoicePermission->name = 'invoice-send';
        $invoicePermission->description = "Be able to set an invoice as send to an customer (Or Send it if billing integration is active)";
        $invoicePermission->grouping = 'invoice';
        $invoicePermission->save();

        $invoicePermission = new Permission;
        $invoicePermission->display_name = "Set an invoice as paid";
        $invoicePermission->name = 'invoice-pay';
        $invoicePermission->description = "Be able to set an invoice as paid or not paid";
        $invoicePermission->grouping = 'invoice';
        $invoicePermission->save();

        /**
        * Leads Permission
        */
        $createLead = new Permission;
        $createLead->display_name = 'Create lead';
        $createLead->name = 'lead-create';
        $createLead->description = 'Permission to create lead';
        $createLead->grouping = 'lead';
        $createLead->save();

        $updateLead = new Permission;
        $updateLead->display_name = 'Update lead status';
        $updateLead->name = 'lead-update-status';
        $updateLead->description = 'Permission to update lead status';
        $updateLead->grouping = 'lead';
        $updateLead->save();

        $updateLead = new Permission;
        $updateLead->display_name = 'Change lead deadline';
        $updateLead->name = 'lead-update-deadline';
        $updateLead->description = 'Permission to update a lead deadline';
        $updateLead->grouping = 'lead';
        $updateLead->save();

        $assignNewUserLead = new Permission();
        $assignNewUserLead->display_name = 'Change assigned user';
        $assignNewUserLead->name = 'can-assign-new-user-to-lead';
        $assignNewUserLead->description = 'Permission to change the assigned user on a lead';
        $assignNewUserLead->grouping = 'lead';
        $assignNewUserLead->save();

        /**
        * Project Permission
        */
        
        $createproject = new Permission;
        $createproject->display_name = 'Create project';
        $createproject->name = 'project-create';
        $createproject->description = 'Permission to create project';
        $createproject->grouping = 'project';
        $createproject->save();

        $updateproject = new Permission;
        $updateproject->display_name = 'Update project status';
        $updateproject->name = 'project-update-status';
        $updateproject->description = 'Permission to update project status';
        $updateproject->grouping = 'project';
        $updateproject->save();

        $updateproject = new Permission;
        $updateproject->display_name = 'Change project deadline';
        $updateproject->name = 'project-update-deadline';
        $updateproject->description = 'Permission to update a projects deadline';
        $updateproject->grouping = 'project';
        $updateproject->save();

        $assignNewUserproject = new Permission();
        $assignNewUserproject->display_name = 'Change assigned user';
        $assignNewUserproject->name = 'can-assign-new-user-to-project';
        $assignNewUserproject->description = 'Permission to change the assigned user on a project';
        $assignNewUserproject->grouping = 'project';
        $assignNewUserproject->save();

        $projectUploadFiles = new Permission();
        $projectUploadFiles->display_name = 'Upload files to project';
        $projectUploadFiles->name = 'project-upload-files';
        $projectUploadFiles->description = 'Allowed to upload files for a project';
        $projectUploadFiles->grouping = 'project';
        $projectUploadFiles->save();

        Permission::firstOrCreate([
            'display_name' => 'Add payment',
            'name' => 'payment-create',
            'description' => 'Be able to add a new payment on a invoice',
            'grouping' => 'payment',
        ]);

        Permission::firstOrCreate([
            'display_name' => 'Delete payment',
            'name' => 'payment-delete',
            'description' => 'Be able to delete a payment',
            'grouping' => 'payment',
        ]);

        /** Create new permissions */
        Permission::firstOrCreate([
            'display_name' => 'View calendar',
            'name' => 'calendar-view',
            'description' => 'Be able to view the calendar for appointments',
            'grouping' => 'appointment',
        ]);
        /** Create new permissions */
        Permission::firstOrCreate([
            'display_name' => 'Add appointment',
            'name' => 'appointment-create',
            'description' => 'Be able to create a new appointment for a user',
            'grouping' => 'appointment',
        ]);

        /** Create new permissions */
        Permission::firstOrCreate([
            'display_name' => 'Edit appointment',
            'name' => 'appointment-edit',
            'description' => 'Be able to edit appointment such as times and title',
            'grouping' => 'appointment',
        ]);

        Permission::firstOrCreate([
            'display_name' => 'Delete appointment',
            'name' => 'appointment-delete',
            'description' => 'Be able to delete an appointment',
            'grouping' => 'appointment',
        ]);

        Permission::firstOrCreate([
            'display_name' => 'Add Product',
            'name' => 'product-create',
            'description' => 'Be able to create an product',
            'grouping' => 'product',
        ]);

        Permission::firstOrCreate([
            'display_name' => 'Edit product',
            'name' => 'product-edit',
            'description' => 'Be able to edit an product',
            'grouping' => 'product',
        ]);

        Permission::firstOrCreate([
            'display_name' => 'Delete product',
            'name' => 'product-delete',
            'description' => 'Be able to delete an product',
            'grouping' => 'product',
        ]);

        Permission::firstOrCreate([
            'display_name' => 'Add offer',
            'name' => 'offer-create',
            'description' => 'Be able to create an offer',
            'grouping' => 'offer',
        ]);

        Permission::firstOrCreate([
            'display_name' => 'Edit offer',
            'name' => 'offer-edit',
            'description' => 'Be able to edit an offer',
            'grouping' => 'offer',
        ]);

        Permission::firstOrCreate([
            'display_name' => 'Delete offer',
            'name' => 'offer-delete',
            'description' => 'Be able to delete an offer',
            'grouping' => 'offer',
        ]);

    }
}
