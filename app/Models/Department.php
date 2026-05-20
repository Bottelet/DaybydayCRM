<?php

namespace App\Models;

use App\Traits\HasExternalId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasExternalId;
    use HasFactory;

    public const MANAGEMENT = 'Management';

    protected $fillable
        = [
            'name',
            'external_id',
            'description',
        ];

    protected $hidden = ['pivot'];

    # region Relationships

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    # endregion
}
