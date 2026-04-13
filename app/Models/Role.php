<?php

namespace App\Models;

use App\Enums\RoleType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasExternalId;
use App\Zizaco\Entrust\EntrustRole;

class Role extends EntrustRole
{
    use HasExternalId;
    use HasFactory;

    /**
     * @deprecated Use RoleType::OWNER->value instead
     */
    public const OWNER_ROLE = 'owner';

    /**
     * @deprecated Use RoleType::ADMINISTRATOR->value instead
     */
    public const ADMIN_ROLE = 'administrator';

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'external_id',
    ];

    // region Relationships

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_role', 'role_id', 'permission_id');
    }

    public function userRole()
    {
        return $this->hasMany(RoleUser::class, 'role_id', 'id');
    }

    // endregion

    public function canBeDeleted()
    {
        $roleType = RoleType::fromString($this->name);
        return $roleType ? $roleType->canBeDeleted() : true;
    }
}
