<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermissionRole extends Model
{
    use HasFactory;

    protected $table = 'permission_role';

    protected $fillable = [
        'permission_id',
        'role_id',
    ];

    public $timestamps = false;

    # region Relationships

    public function employee()
    {
        return $this->hasMany(PermissionRole::class, 'role_id', 3);
    }

    public function hasperm()
    {
        return $this->hasMany(Permission::class, 'Permission_role');
    }

    public function settings()
    {
        return $this->belongsTo(Setting::class);
    }

    # endregion
}
