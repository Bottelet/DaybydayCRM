<?php

namespace App\Enums;

enum PermissionName: string
{
    // User Management
    case USER_UPDATE = 'user-update';
    case USER_DELETE = 'user-delete';
    case USER_VIEW = 'user-view';

    // Payment Management
    case PAYMENT_CREATE = 'payment-create';
    case PAYMENT_UPDATE = 'payment-update';
    case PAYMENT_DELETE = 'payment-delete';

    // Appointment Management
    case APPOINTMENT_EDIT = 'appointment-edit';
    case APPOINTMENT_DELETE = 'appointment-delete';
    case APPOINTMENT_CREATE = 'appointment-create';
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

    // Offer/Project Management (Found in 403 logs)
    case OFFER_DELETE = 'offer-delete';
    case PROJECT_UPDATE = 'project-update';

    // Document Management
    case DOCUMENT_VIEW = 'document-view';

    /**
     * Helper to get labels for Entrust's display_name
     */
    public function label(): string
    {
        return match($this) {
            self::USER_UPDATE => 'Update User',
            self::USER_DELETE => 'Delete User',
            self::PAYMENT_CREATE => 'Create Payment',
            self::PAYMENT_DELETE => 'Delete Payment',
            self::APPOINTMENT_EDIT => 'Edit Appointment',
            self::APPOINTMENT_DELETE => 'Delete Appointment',
            self::CALENDAR_VIEW => 'View Calendar',
            self::CLIENT_CREATE => 'Create Client',
            self::CLIENT_UPDATE => 'Update Client',
            self::CLIENT_DELETE => 'Delete Client',
            self::LEAD_DELETE => 'Delete Lead',
            self::LEAD_UPDATE_STATUS => 'Update Lead Status',
            self::LEAD_UPDATE_DEADLINE => 'Update Lead Deadline',
            self::LEAD_ASSIGN => 'Assign Lead',
            self::ABSENCE_MANAGE => 'Manage Absence',
            self::DOCUMENT_VIEW => 'View Document',
            default => ucfirst(str_replace('-', ' ', $this->value)),
        };
    }
}
