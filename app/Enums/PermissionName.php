<?php

namespace App\Enums;

enum PermissionName: string
{
    // User Management
    case USER_CREATE = 'user-create';
    case USER_UPDATE = 'user-update';
    case USER_DELETE = 'user-delete';
    case USER_VIEW = 'user-view';

    // Payment Management
    case PAYMENT_CREATE = 'payment-create';
    case PAYMENT_UPDATE = 'payment-update';
    case PAYMENT_DELETE = 'payment-delete';

    // Appointment Management
    case APPOINTMENT_CREATE = 'appointment-create';
    case APPOINTMENT_EDIT = 'appointment-edit';
    case APPOINTMENT_DELETE = 'appointment-delete';
    case CALENDAR_VIEW = 'calendar-view';

    // Client Management
    case CLIENT_CREATE = 'client-create';
    case CLIENT_UPDATE = 'client-update';
    case CLIENT_DELETE = 'client-delete';
    case CLIENT_VIEW = 'client-view';

    // Lead Management
    case LEAD_CREATE = 'lead-create';
    case LEAD_UPDATE_STATUS = 'lead-update-status';
    case LEAD_DELETE = 'lead-delete';
    case LEAD_VIEW = 'lead-view';
    case LEAD_UPDATE_DEADLINE = 'lead-update-deadline';
    case LEAD_ASSIGN = 'can-assign-new-user-to-lead';

    // Absence Management
    case ABSENCE_MANAGE = 'absence-manage';
    case ABSENCE_VIEW = 'absence-view';

    // Offer Management
    case OFFER_DELETE = 'offer-delete';

    // Project Management
    case PROJECT_CREATE = 'project-create';
    case PROJECT_UPDATE = 'project-update';
    case PROJECT_DELETE = 'project-delete';
    case PROJECT_UPDATE_STATUS = 'project-update-status';
    case PROJECT_UPDATE_DEADLINE = 'project-update-deadline';
    case PROJECT_UPDATE_ASSIGNMENT = 'project-update-assignment';
    case PROJECT_ASSIGN = 'can-assign-new-user-to-project';
    case PROJECT_UPLOAD_FILES = 'project-upload-files';

    // Task Management
    case TASK_CREATE = 'task-create';
    case TASK_DELETE = 'task-delete';
    case TASK_UPDATE_STATUS = 'task-update-status';
    case TASK_UPDATE_DEADLINE = 'task-update-deadline';
    case TASK_UPDATE_ASSIGNMENT = 'task-update-assignment';
    case TASK_UPDATE_LINKED_PROJECT = 'task-update-linked-project';
    case TASK_ASSIGN = 'can-assign-new-user-to-task';
    case TASK_UPLOAD_FILES = 'task-upload-files';

    // Document Management
    case DOCUMENT_VIEW = 'document-view';
    case DOCUMENT_DELETE = 'document-delete';
    case DOCUMENT_UPLOAD = 'document-upload';

    // Invoice Management
    case INVOICE_SEE = 'invoice-see';
    case INVOICE_SEND = 'invoice-send';
    case MODIFY_INVOICE_LINES = 'modify-invoice-lines';

    // Product Management
    case PRODUCT_CREATE = 'product-create';
    case PRODUCT_EDIT = 'product-edit';
    case PRODUCT_DELETE = 'product-delete';

    /**
     * Helper to get labels for Entrust's display_name
     */
    public function label(): string
    {
        return match($this) {
            self::USER_CREATE => 'Create User',
            self::USER_UPDATE => 'Update User',
            self::USER_DELETE => 'Delete User',
            self::USER_VIEW => 'View User',
            self::PAYMENT_CREATE => 'Create Payment',
            self::PAYMENT_UPDATE => 'Update Payment',
            self::PAYMENT_DELETE => 'Delete Payment',
            self::APPOINTMENT_CREATE => 'Create Appointment',
            self::APPOINTMENT_EDIT => 'Edit Appointment',
            self::APPOINTMENT_DELETE => 'Delete Appointment',
            self::CALENDAR_VIEW => 'View Calendar',
            self::CLIENT_CREATE => 'Create Client',
            self::CLIENT_UPDATE => 'Update Client',
            self::CLIENT_DELETE => 'Delete Client',
            self::CLIENT_VIEW => 'View Client',
            self::LEAD_CREATE => 'Create Lead',
            self::LEAD_DELETE => 'Delete Lead',
            self::LEAD_VIEW => 'View Lead',
            self::LEAD_UPDATE_STATUS => 'Update Lead Status',
            self::LEAD_UPDATE_DEADLINE => 'Update Lead Deadline',
            self::LEAD_ASSIGN => 'Assign Lead',
            self::ABSENCE_MANAGE => 'Manage Absence',
            self::ABSENCE_VIEW => 'View Absence',
            self::OFFER_DELETE => 'Delete Offer',
            self::PROJECT_CREATE => 'Create Project',
            self::PROJECT_UPDATE => 'Update Project',
            self::PROJECT_DELETE => 'Delete Project',
            self::PROJECT_UPDATE_STATUS => 'Update Project Status',
            self::PROJECT_UPDATE_DEADLINE => 'Update Project Deadline',
            self::PROJECT_UPDATE_ASSIGNMENT => 'Update Project Assignment',
            self::PROJECT_ASSIGN => 'Assign Project',
            self::PROJECT_UPLOAD_FILES => 'Upload Project Files',
            self::TASK_CREATE => 'Create Task',
            self::TASK_DELETE => 'Delete Task',
            self::TASK_UPDATE_STATUS => 'Update Task Status',
            self::TASK_UPDATE_DEADLINE => 'Update Task Deadline',
            self::TASK_UPDATE_ASSIGNMENT => 'Update Task Assignment',
            self::TASK_UPDATE_LINKED_PROJECT => 'Update Task Linked Project',
            self::TASK_ASSIGN => 'Assign Task',
            self::TASK_UPLOAD_FILES => 'Upload Task Files',
            self::DOCUMENT_VIEW => 'View Document',
            self::DOCUMENT_DELETE => 'Delete Document',
            self::DOCUMENT_UPLOAD => 'Upload Document',
            self::INVOICE_SEE => 'View Invoice',
            self::INVOICE_SEND => 'Send Invoice',
            self::MODIFY_INVOICE_LINES => 'Modify Invoice Lines',
            self::PRODUCT_CREATE => 'Create Product',
            self::PRODUCT_EDIT => 'Edit Product',
            self::PRODUCT_DELETE => 'Delete Product',
            default => ucfirst(str_replace('-', ' ', $this->value)),
        };
    }
}
