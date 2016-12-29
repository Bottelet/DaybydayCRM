<?php
namespace App\Models;

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
        return $this->belongsTo(Setting::class);
    }

    public function employee()
    {
        return $this->hasMany(PermissionRole::class, 'role_id', 3);
    }

    public function hasperm()
    {
        return $this->hasMany(Permissions::class, 'Permission_role');
    }
}
