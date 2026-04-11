<?

namespace App\Enums;

enum PermissionName: string
{
    case APPOINTMENT_EDIT = 'appointment-edit';
    case ABSENCE_MANAGE = 'absence-manage';
    case CLIENT_CREATE = 'client-create';

/*
        $permissions = [
            'user-update' => ['display_name' => 'Update User', 'description' => 'Update user permission'],
            'user-delete' => ['display_name' => 'Delete User', 'description' => 'Delete user permission'],
            'payment-create' => ['display_name' => 'Create Payment', 'description' => 'Create payment permission'],
            'payment-delete' => ['display_name' => 'Delete Payment', 'description' => 'Delete payment permission'],
            'appointment-edit' => ['display_name' => 'Edit Appointment', 'description' => 'Edit appointment permission'],
            'appointment-delete' => ['display_name' => 'Delete Appointment', 'description' => 'Delete appointment permission'],
            'calendar-view' => ['display_name' => 'View Calendar', 'description' => 'View calendar permission'],
            'client-create' => ['display_name' => 'Create Client', 'description' => 'Create client permission'],
            'client-update' => ['display_name' => 'Update Client', 'description' => 'Update client permission'],
            'client-delete' => ['display_name' => 'Delete Client', 'description' => 'Delete client permission'],
            'lead-delete' => ['display_name' => 'Delete Lead', 'description' => 'Delete lead permission'],
            'absence-manage' => ['display_name' => 'Manage Absence', 'description' => 'Manage absence permission'],
        ];
*/

}
