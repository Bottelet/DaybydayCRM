<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permissions extends Model
{
    public function roles()
    {
        return $this->belongsToMany('App\Role', 'permission_role', 'permission_id', 'role_id');
    }
    public function hasperm()
    {
        return $this->belongsToMany('App\PermissionRole', 'Permission_role', 'role_id');
    }
}
