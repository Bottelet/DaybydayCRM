<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
    'name',
    'slug',
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
