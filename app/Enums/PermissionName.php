<?php

namespace App\Enums;

enum PermissionName: string
{
    // User Management
    case USER_CREATE = 'user-create';
    case USER_UPDATE = 'user-update';
    case USER_DELETE = 'user-delete';
    case USER_VIEW   = 'user-view';

    // Payment Management
    case PAYMENT_CREATE = 'payment-create';
    case PAYMENT_UPDATE = 'payment-update';
    case PAYMENT_DELETE = 'payment-delete';

    // Appointment Management
    case APPOINTMENT_CREATE = 'appointment-create';
    case APPOINTMENT_EDIT   = 'appointment-edit';
    case APPOINTMENT_DELETE = 'appointment-delete';
    case CALENDAR_VIEW      = 'calendar-view';

    // Client Management
    case CLIENT_CREATE = 'client-create';
    case CLIENT_UPDATE = 'client-update';
    case CLIENT_DELETE = 'client-delete';
    case CLIENT_VIEW   = 'client-view';

    // Lead Management
    case LEAD_CREATE          = 'lead-create';
    case LEAD_UPDATE          = 'lead-update';
    case LEAD_UPDATE_STATUS   = 'lead-update-status';
    case LEAD_DELETE          = 'lead-delete';
    case LEAD_VIEW            = 'lead-view';
    case LEAD_UPDATE_DEADLINE = 'lead-update-deadline';
    case LEAD_ASSIGN          = 'can-assign-new-user-to-lead';

    // Absence Management
    case ABSENCE_MANAGE = 'absence-manage';
    case ABSENCE_VIEW   = 'absence-view';

    // Offer Management
    case OFFER_CREATE = 'offer-create';
    case OFFER_EDIT   = 'offer-edit';
    case OFFER_DELETE = 'offer-delete';
    case OFFER_VIEW   = 'offer-view';

    // Project Management
    case PROJECT_CREATE            = 'project-create';
    case PROJECT_UPDATE            = 'project-update';
    case PROJECT_DELETE            = 'project-delete';
    case PROJECT_VIEW              = 'project-view';
    case PROJECT_UPDATE_STATUS     = 'project-update-status';
    case PROJECT_UPDATE_DEADLINE   = 'project-update-deadline';
    case PROJECT_UPDATE_ASSIGNMENT = 'project-update-assignment';
    case PROJECT_ASSIGN            = 'can-assign-new-user-to-project';
    case PROJECT_UPLOAD_FILES      = 'project-upload-files';

    // Task Management
    case TASK_CREATE                = 'task-create';
    case TASK_UPDATE                = 'task-update';
    case TASK_DELETE                = 'task-delete';
    case TASK_VIEW                  = 'task-view';
    case TASK_UPDATE_STATUS         = 'task-update-status';
    case TASK_UPDATE_DEADLINE       = 'task-update-deadline';
    case TASK_UPDATE_ASSIGNMENT     = 'task-update-assignment';
    case TASK_UPDATE_LINKED_PROJECT = 'task-update-linked-project';
    case TASK_ASSIGN                = 'can-assign-new-user-to-task';
    case TASK_UPLOAD_FILES          = 'task-upload-files';

    // Document Management
    case DOCUMENT_VIEW   = 'document-view';
    case DOCUMENT_DELETE = 'document-delete';
    case DOCUMENT_UPLOAD = 'document-upload';

    // Invoice Management
    case INVOICE_CREATE       = 'invoice-create';
    case INVOICE_UPDATE       = 'invoice-update';
    case INVOICE_DELETE       = 'invoice-delete';
    case INVOICE_SEE          = 'invoice-see';
    case INVOICE_SEND         = 'invoice-send';
    case INVOICE_PAY          = 'invoice-pay';
    case MODIFY_INVOICE_LINES = 'modify-invoice-lines';

    // Product Management
    case PRODUCT_CREATE = 'product-create';
    case PRODUCT_EDIT   = 'product-edit';
    case PRODUCT_DELETE = 'product-delete';
    case PRODUCT_VIEW   = 'product-view';

    /**
     * Generate permission metadata for seeders and upgrade command.
     * This is the single source of truth for all permissions.
     *
     * @return array<string, array{display_name: string, description: string, grouping: string}>
     */
    public static function allPermissions(): array
    {
        $permissions = [];
        foreach (self::cases() as $case) {
            $permissions[$case->value] = [
                'display_name' => $case->label(),
                'description'  => $case->description(),
                'grouping'     => $case->grouping(),
            ];
        }

        return $permissions;
    }

    /**
     * Helper to get labels for Entrust's display_name.
     */
    public function label(): string
    {
        return match($this) {
            self::USER_CREATE                => 'Create User',
            self::USER_UPDATE                => 'Update User',
            self::USER_DELETE                => 'Delete User',
            self::USER_VIEW                  => 'View User',
            self::PAYMENT_CREATE             => 'Create Payment',
            self::PAYMENT_UPDATE             => 'Update Payment',
            self::PAYMENT_DELETE             => 'Delete Payment',
            self::APPOINTMENT_CREATE         => 'Create Appointment',
            self::APPOINTMENT_EDIT           => 'Edit Appointment',
            self::APPOINTMENT_DELETE         => 'Delete Appointment',
            self::CALENDAR_VIEW              => 'View Calendar',
            self::CLIENT_CREATE              => 'Create Client',
            self::CLIENT_UPDATE              => 'Update Client',
            self::CLIENT_DELETE              => 'Delete Client',
            self::CLIENT_VIEW                => 'View Client',
            self::LEAD_CREATE                => 'Create Lead',
            self::LEAD_UPDATE                => 'Update Lead',
            self::LEAD_DELETE                => 'Delete Lead',
            self::LEAD_VIEW                  => 'View Lead',
            self::LEAD_UPDATE_STATUS         => 'Update Lead Status',
            self::LEAD_UPDATE_DEADLINE       => 'Update Lead Deadline',
            self::LEAD_ASSIGN                => 'Assign Lead',
            self::ABSENCE_MANAGE             => 'Manage Absence',
            self::ABSENCE_VIEW               => 'View Absence',
            self::OFFER_CREATE               => 'Create Offer',
            self::OFFER_EDIT                 => 'Edit Offer',
            self::OFFER_DELETE               => 'Delete Offer',
            self::OFFER_VIEW                 => 'View Offer',
            self::PROJECT_CREATE             => 'Create Project',
            self::PROJECT_UPDATE             => 'Update Project',
            self::PROJECT_DELETE             => 'Delete Project',
            self::PROJECT_VIEW               => 'View Project',
            self::PROJECT_UPDATE_STATUS      => 'Update Project Status',
            self::PROJECT_UPDATE_DEADLINE    => 'Update Project Deadline',
            self::PROJECT_UPDATE_ASSIGNMENT  => 'Update Project Assignment',
            self::PROJECT_ASSIGN             => 'Assign Project',
            self::PROJECT_UPLOAD_FILES       => 'Upload Project Files',
            self::TASK_CREATE                => 'Create Task',
            self::TASK_UPDATE                => 'Update Task',
            self::TASK_DELETE                => 'Delete Task',
            self::TASK_VIEW                  => 'View Task',
            self::TASK_UPDATE_STATUS         => 'Update Task Status',
            self::TASK_UPDATE_DEADLINE       => 'Update Task Deadline',
            self::TASK_UPDATE_ASSIGNMENT     => 'Update Task Assignment',
            self::TASK_UPDATE_LINKED_PROJECT => 'Update Task Linked Project',
            self::TASK_ASSIGN                => 'Assign Task',
            self::TASK_UPLOAD_FILES          => 'Upload Task Files',
            self::DOCUMENT_VIEW              => 'View Document',
            self::DOCUMENT_DELETE            => 'Delete Document',
            self::DOCUMENT_UPLOAD            => 'Upload Document',
            self::INVOICE_CREATE             => 'Create Invoice',
            self::INVOICE_UPDATE             => 'Update Invoice',
            self::INVOICE_DELETE             => 'Delete Invoice',
            self::INVOICE_SEE                => 'View Invoice',
            self::INVOICE_SEND               => 'Send Invoice',
            self::INVOICE_PAY                => 'Set Invoice as Paid',
            self::MODIFY_INVOICE_LINES       => 'Modify Invoice Lines',
            self::PRODUCT_CREATE             => 'Create Product',
            self::PRODUCT_EDIT               => 'Edit Product',
            self::PRODUCT_DELETE             => 'Delete Product',
            self::PRODUCT_VIEW               => 'View Product',
            default                          => ucfirst(str_replace('-', ' ', $this->value)),
        };
    }

    /**
     * Get description for a permission.
     */
    public function description(): string
    {
        return match($this) {
            self::USER_CREATE                => 'Be able to create a new user',
            self::USER_UPDATE                => "Be able to update a user's information",
            self::USER_DELETE                => 'Be able to delete a user',
            self::USER_VIEW                  => 'Be able to view users',
            self::PAYMENT_CREATE             => 'Be able to add a new payment on a invoice',
            self::PAYMENT_UPDATE             => 'Be able to update a payment',
            self::PAYMENT_DELETE             => 'Be able to delete a payment',
            self::APPOINTMENT_CREATE         => 'Be able to create a new appointment for a user',
            self::APPOINTMENT_EDIT           => 'Be able to edit appointment such as times and title',
            self::APPOINTMENT_DELETE         => 'Be able to delete an appointment',
            self::CALENDAR_VIEW              => 'Be able to view the calendar for appointments',
            self::CLIENT_CREATE              => 'Permission to create client',
            self::CLIENT_UPDATE              => 'Permission to update client',
            self::CLIENT_DELETE              => 'Permission to delete client',
            self::CLIENT_VIEW                => 'Permission to view clients',
            self::LEAD_CREATE                => 'Permission to create lead',
            self::LEAD_UPDATE                => 'Permission to update lead',
            self::LEAD_DELETE                => 'Permission to delete a lead',
            self::LEAD_VIEW                  => 'Permission to view leads',
            self::LEAD_UPDATE_STATUS         => 'Permission to update lead status',
            self::LEAD_UPDATE_DEADLINE       => 'Permission to update a lead deadline',
            self::LEAD_ASSIGN                => 'Permission to change the assigned user on a lead',
            self::ABSENCE_MANAGE             => 'Be able to manage absence',
            self::ABSENCE_VIEW               => 'Be able to view absence',
            self::OFFER_CREATE               => 'Be able to create an offer',
            self::OFFER_EDIT                 => 'Be able to edit an offer',
            self::OFFER_DELETE               => 'Be able to delete an offer',
            self::OFFER_VIEW                 => 'Be able to view offers',
            self::PROJECT_CREATE             => 'Permission to create project',
            self::PROJECT_UPDATE             => 'Permission to update project',
            self::PROJECT_DELETE             => 'Permission to delete project',
            self::PROJECT_VIEW               => 'Permission to view projects',
            self::PROJECT_UPDATE_STATUS      => 'Permission to update project status',
            self::PROJECT_UPDATE_DEADLINE    => 'Permission to update a projects deadline',
            self::PROJECT_UPDATE_ASSIGNMENT  => 'Permission to update project assignment',
            self::PROJECT_ASSIGN             => 'Permission to change the assigned user on a project',
            self::PROJECT_UPLOAD_FILES       => 'Allowed to upload files for a project',
            self::TASK_CREATE                => 'Permission to create task',
            self::TASK_UPDATE                => 'Permission to update task',
            self::TASK_DELETE                => 'Permission to delete a task',
            self::TASK_VIEW                  => 'Permission to view tasks',
            self::TASK_UPDATE_STATUS         => 'Permission to update task status',
            self::TASK_UPDATE_DEADLINE       => 'Permission to update a tasks deadline',
            self::TASK_UPDATE_ASSIGNMENT     => 'Permission to update task assignment',
            self::TASK_UPDATE_LINKED_PROJECT => 'Be able to change the project which is linked to a task',
            self::TASK_ASSIGN                => 'Permission to change the assigned user on a task',
            self::TASK_UPLOAD_FILES          => 'Allowed to upload files for a task',
            self::DOCUMENT_VIEW              => 'Permission to view documents',
            self::DOCUMENT_DELETE            => 'Permission to delete a document associated with a client',
            self::DOCUMENT_UPLOAD            => 'Be able to upload a document associated with a client',
            self::INVOICE_CREATE             => 'Permission to create invoices',
            self::INVOICE_UPDATE             => 'Permission to update invoices',
            self::INVOICE_DELETE             => 'Permission to delete invoices',
            self::INVOICE_SEE                => "Permission to see invoices on customer, and it's associated task",
            self::INVOICE_SEND               => 'Be able to set an invoice as send to an customer (Or Send it if billing integration is active)',
            self::INVOICE_PAY                => 'Be able to set an invoice as paid or not paid',
            self::MODIFY_INVOICE_LINES       => 'Permission to create and update invoice lines on task, and invoices',
            self::PRODUCT_CREATE             => 'Be able to create an product',
            self::PRODUCT_EDIT               => 'Be able to edit an product',
            self::PRODUCT_DELETE             => 'Be able to delete an product',
            self::PRODUCT_VIEW               => 'Be able to view products',
            default                          => ucfirst(str_replace('-', ' ', $this->value)),
        };
    }

    /**
     * Get grouping for a permission.
     */
    public function grouping(): string
    {
        return match($this) {
            self::USER_CREATE, self::USER_UPDATE, self::USER_DELETE, self::USER_VIEW         => 'user',
            self::CLIENT_CREATE, self::CLIENT_UPDATE, self::CLIENT_DELETE, self::CLIENT_VIEW => 'client',
            self::DOCUMENT_VIEW, self::DOCUMENT_DELETE, self::DOCUMENT_UPLOAD                => 'document',
            self::TASK_CREATE, self::TASK_UPDATE, self::TASK_DELETE, self::TASK_VIEW, self::TASK_UPDATE_STATUS, self::TASK_UPDATE_DEADLINE,
            self::TASK_UPDATE_ASSIGNMENT, self::TASK_UPDATE_LINKED_PROJECT, self::TASK_ASSIGN, self::TASK_UPLOAD_FILES                                                                                                                          => 'task',
            self::MODIFY_INVOICE_LINES, self::INVOICE_CREATE, self::INVOICE_UPDATE, self::INVOICE_DELETE, self::INVOICE_SEE, self::INVOICE_SEND, self::INVOICE_PAY                                                                              => 'invoice',
            self::LEAD_CREATE, self::LEAD_UPDATE, self::LEAD_UPDATE_STATUS, self::LEAD_UPDATE_DEADLINE, self::LEAD_ASSIGN, self::LEAD_DELETE, self::LEAD_VIEW                                                                                   => 'lead',
            self::PROJECT_CREATE, self::PROJECT_VIEW, self::PROJECT_UPDATE_STATUS, self::PROJECT_UPDATE_DEADLINE, self::PROJECT_ASSIGN, self::PROJECT_UPLOAD_FILES, self::PROJECT_UPDATE, self::PROJECT_DELETE, self::PROJECT_UPDATE_ASSIGNMENT => 'project',
            self::PAYMENT_CREATE, self::PAYMENT_DELETE, self::PAYMENT_UPDATE                                                                                                                                                                    => 'payment',
            self::CALENDAR_VIEW, self::APPOINTMENT_CREATE, self::APPOINTMENT_EDIT, self::APPOINTMENT_DELETE                                                                                                                                     => 'appointment',
            self::PRODUCT_CREATE, self::PRODUCT_EDIT, self::PRODUCT_DELETE, self::PRODUCT_VIEW                                                                                                                                                  => 'product',
            self::OFFER_CREATE, self::OFFER_EDIT, self::OFFER_DELETE, self::OFFER_VIEW                                                                                                                                                          => 'offer',
            self::ABSENCE_MANAGE, self::ABSENCE_VIEW                                                                                                                                                                                            => 'absence',
            default                                                                                                                                                                                                                             => 'general',
        };
    }
}
