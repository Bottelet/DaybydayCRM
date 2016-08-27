<?php
namespace App;

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
        return $this->hasMany('Role', 'user_id', 'id');
    }

    public function permissions()
    {
        return $this->belongsToMany('App\Permissions', 'permission_role', 'role_id', 'permission_id');
    }
}
