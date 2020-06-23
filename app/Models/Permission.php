<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string display_name
 * @property string name
 * @property string description
 * @property string grouping
 */
class Permission extends Model
{
    protected $fillable = [
      'display_name',
      'name',
      'description',
      'grouping'
    ];
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'permission_role', 'permission_id', 'role_id');
    }
}
