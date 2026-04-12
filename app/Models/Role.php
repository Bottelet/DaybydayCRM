<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasExternalId;
use App\Zizaco\Entrust\EntrustRole;

class Role extends EntrustRole
{
    use HasExternalId;
    use HasFactory;

    public const OWNER_ROLE = 'owner';

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
        return $this->name !== Role::ADMIN_ROLE && $this->name !== Role::OWNER_ROLE;
    }
}
