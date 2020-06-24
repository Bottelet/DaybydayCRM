<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Zizaco\Entrust\EntrustRole;
use App\Models\Permission;

class Role extends EntrustRole
{
    const OWNER_ROLE = "owner";
    const ADMIN_ROLE = "administrator";

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'external_id',
    ];

    public function userRole()
    {
        return $this->hasMany(Role::class, 'user_id', 'id');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_role', 'role_id', 'permission_id');
    }

    public function canBeDeleted()
    {
        return $this->name !== Role::ADMIN_ROLE && $this->name !== Role::OWNER_ROLE;
    }
}
