<?

namespace App\Enums;

enum PermissionName: string
{
    case APPOINTMENT_EDIT = 'appointment-edit';
    case ABSENCE_MANAGE = 'absence-manage';
    case CLIENT_CREATE = 'client-create';
    // ... add all cases found in your asOwner() method
}
