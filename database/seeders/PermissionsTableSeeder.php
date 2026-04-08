<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            // User
            [
                'display_name' => 'Create user',
                'name' => 'user-create',
                'description' => 'Be able to create a new user',
                'grouping' => 'user',
            ],
            [
                'display_name' => 'Update user',
                'name' => 'user-update',
                'description' => "Be able to update a user's information",
                'grouping' => 'user',
            ],
            [
                'display_name' => 'Delete user',
                'name' => 'user-delete',
                'description' => 'Be able to delete a user',
                'grouping' => 'user',
            ],
            // Client
            [
                'display_name' => 'Create client',
                'name' => 'client-create',
                'description' => 'Permission to create client',
                'grouping' => 'client',
            ],
            [
                'display_name' => 'Update client',
                'name' => 'client-update',
                'description' => 'Permission to update client',
                'grouping' => 'client',
            ],
            [
                'display_name' => 'Delete client',
                'name' => 'client-delete',
                'description' => 'Permission to delete client',
                'grouping' => 'client',
            ],
            // Document
            [
                'display_name' => 'Delete document',
                'name' => 'document-delete',
                'description' => 'Permission to delete a document associated with a client',
                'grouping' => 'document',
            ],
            [
                'display_name' => 'Upload document',
                'name' => 'document-upload',
                'description' => 'Be able to upload a document associated with a client',
                'grouping' => 'document',
            ],
            // Task
            [
                'display_name' => 'Create task',
                'name' => 'task-create',
                'description' => 'Permission to create task',
                'grouping' => 'task',
            ],
            [
                'display_name' => 'Update task status',
                'name' => 'task-update-status',
                'description' => 'Permission to update task status',
                'grouping' => 'task',
            ],
            [
                'display_name' => 'Change task deadline',
                'name' => 'task-update-deadline',
                'description' => 'Permission to update a tasks deadline',
                'grouping' => 'task',
            ],
            [
                'display_name' => 'Change assigned user',
                'name' => 'can-assign-new-user-to-task',
                'description' => 'Permission to change the assigned user on a task',
                'grouping' => 'task',
            ],
            [
                'display_name' => 'Changed linked project',
                'name' => 'task-update-linked-project',
                'description' => 'Be able to change the project which is linked to a task',
                'grouping' => 'task',
            ],
            [
                'display_name' => 'Upload files to task',
                'name' => 'task-upload-files',
                'description' => 'Allowed to upload files for a task',
                'grouping' => 'task',
            ],
            [
                'display_name' => 'Delete task',
                'name' => 'task-delete',
                'description' => 'Permission to delete a task',
                'grouping' => 'task',
            ],
            // Invoice
            [
                'display_name' => 'Modify invoice lines on a invoice / task',
                'name' => 'modify-invoice-lines',
                'description' => 'Permission to create and update invoice lines on task, and invoices',
                'grouping' => 'invoice',
            ],
            [
                'display_name' => "See invoices and it's invoice lines",
                'name' => 'invoice-see',
                'description' => "Permission to see invoices on customer, and it's associated task",
                'grouping' => 'invoice',
            ],
            [
                'display_name' => 'Send invoices to clients',
                'name' => 'invoice-send',
                'description' => 'Be able to set an invoice as send to an customer (Or Send it if billing integration is active)',
                'grouping' => 'invoice',
            ],
            [
                'display_name' => 'Set an invoice as paid',
                'name' => 'invoice-pay',
                'description' => 'Be able to set an invoice as paid or not paid',
                'grouping' => 'invoice',
            ],
            // Lead
            [
                'display_name' => 'Create lead',
                'name' => 'lead-create',
                'description' => 'Permission to create lead',
                'grouping' => 'lead',
            ],
            [
                'display_name' => 'Update lead status',
                'name' => 'lead-update-status',
                'description' => 'Permission to update lead status',
                'grouping' => 'lead',
            ],
            [
                'display_name' => 'Change lead deadline',
                'name' => 'lead-update-deadline',
                'description' => 'Permission to update a lead deadline',
                'grouping' => 'lead',
            ],
            [
                'display_name' => 'Change assigned user',
                'name' => 'can-assign-new-user-to-lead',
                'description' => 'Permission to change the assigned user on a lead',
                'grouping' => 'lead',
            ],
            [
                'display_name' => 'Delete lead',
                'name' => 'lead-delete',
                'description' => 'Permission to delete a lead',
                'grouping' => 'lead',
            ],
            // Project
            [
                'display_name' => 'Create project',
                'name' => 'project-create',
                'description' => 'Permission to create project',
                'grouping' => 'project',
            ],
            [
                'display_name' => 'Update project status',
                'name' => 'project-update-status',
                'description' => 'Permission to update project status',
                'grouping' => 'project',
            ],
            [
                'display_name' => 'Change project deadline',
                'name' => 'project-update-deadline',
                'description' => 'Permission to update a projects deadline',
                'grouping' => 'project',
            ],
            [
                'display_name' => 'Change assigned user',
                'name' => 'can-assign-new-user-to-project',
                'description' => 'Permission to change the assigned user on a project',
                'grouping' => 'project',
            ],
            [
                'display_name' => 'Upload files to project',
                'name' => 'project-upload-files',
                'description' => 'Allowed to upload files for a project',
                'grouping' => 'project',
            ],
            [
                'display_name' => 'Delete project',
                'name' => 'project-delete',
                'description' => 'Permission to delete a project',
                'grouping' => 'project',
            ],
            // Payment
            [
                'display_name' => 'Add payment',
                'name' => 'payment-create',
                'description' => 'Be able to add a new payment on a invoice',
                'grouping' => 'payment',
            ],
            [
                'display_name' => 'Delete payment',
                'name' => 'payment-delete',
                'description' => 'Be able to delete a payment',
                'grouping' => 'payment',
            ],
            // Calendar/Appointment
            [
                'display_name' => 'View calendar',
                'name' => 'calendar-view',
                'description' => 'Be able to view the calendar for appointments',
                'grouping' => 'appointment',
            ],
            [
                'display_name' => 'Add appointment',
                'name' => 'appointment-create',
                'description' => 'Be able to create a new appointment for a user',
                'grouping' => 'appointment',
            ],
            [
                'display_name' => 'Edit appointment',
                'name' => 'appointment-edit',
                'description' => 'Be able to edit appointment such as times and title',
                'grouping' => 'appointment',
            ],
            [
                'display_name' => 'Delete appointment',
                'name' => 'appointment-delete',
                'description' => 'Be able to delete an appointment',
                'grouping' => 'appointment',
            ],
            // Product
            [
                'display_name' => 'Add Product',
                'name' => 'product-create',
                'description' => 'Be able to create an product',
                'grouping' => 'product',
            ],
            [
                'display_name' => 'Edit product',
                'name' => 'product-edit',
                'description' => 'Be able to edit an product',
                'grouping' => 'product',
            ],
            [
                'display_name' => 'Delete product',
                'name' => 'product-delete',
                'description' => 'Be able to delete an product',
                'grouping' => 'product',
            ],
            // Offer
            [
                'display_name' => 'Add offer',
                'name' => 'offer-create',
                'description' => 'Be able to create an offer',
                'grouping' => 'offer',
            ],
            [
                'display_name' => 'Edit offer',
                'name' => 'offer-edit',
                'description' => 'Be able to edit an offer',
                'grouping' => 'offer',
            ],
            [
                'display_name' => 'Delete offer',
                'name' => 'offer-delete',
                'description' => 'Be able to delete an offer',
                'grouping' => 'offer',
            ],
        ];

        // Always ensure external_id is set for every permission, even if the array is changed in the future
        foreach ($permissions as $perm) {
            $existing = Permission::where('name', $perm['name'])->first();
            if (! $existing) {
                if (! isset($perm['external_id'])) {
                    $perm['external_id'] = Str::uuid()->toString();
                }
                Permission::create($perm);
            }
        }
        // NOTE: If you add new permissions, you must provide an external_id or this code will generate one.
    }
}
