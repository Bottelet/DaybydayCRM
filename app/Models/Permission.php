<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasExternalId;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string display_name
 * @property string name
 * @property string description
 * @property string grouping
 */
class Permission extends Model
{
    use HasExternalId;
    use HasFactory;

    protected $fillable = [
        'external_id',
        'display_name',
        'name',
        'description',
        'grouping',
    ];

    //region Relationships

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'permission_role', 'permission_id', 'role_id');
    }

    //endregion
}
