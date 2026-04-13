<?php

namespace App\Models;

use App\Traits\HasExternalId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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

    # endregion

    # region Static helpers

    /**
     * Get a permission by name, or create it if it does not exist.
     *
     * @param string $name
     * @param array  $attributes
     *
     * @return static
     */
    public static function getOrCreateByName(string $name, array $attributes = [])
    {
        return static::firstOrCreate(['name' => $name], $attributes);
    }

    # region Relationships

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'permission_role', 'permission_id', 'role_id');
    }

    # endregion
}
