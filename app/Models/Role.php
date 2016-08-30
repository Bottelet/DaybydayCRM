<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Zizaco\Entrust\EntrustRole;

class Role extends EntrustRole
{
    protected $fillable = [
    'name',
    'display_name',
    'description'
      ];
      
    public function userRole()
    {
        return $this->hasMany(Role::class, 'user_id', 'id');
    }

    public function permissions()
    {
        return $this->belongsToMany(permissions::class, 'permission_role', 'role_id', 'permissions_id');
    }
}
