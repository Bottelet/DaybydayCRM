<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class PermissionRole extends Model
{
    protected $table = 'permission_role';

    protected $fillable = [
        'permission_id',
        'role_id'
    ];

    public function settings()
    {
        return $this->belongsTo('settings');
    }

    public function employee()
    {
        return $this->hasMany('App\PermissionRole', 'role_id', 3);
    }
    public function hasperm()
    {
        return $this->hasMany('App\Permissions', 'Permission_role');
    }
}
