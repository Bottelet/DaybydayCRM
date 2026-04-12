<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasExternalId;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasExternalId;
    use HasFactory;

    protected $fillable =
        [
            'name',
            'external_id',
            'description',
        ];

    protected $hidden = ['pivot'];

    //region Relationships

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    //endregion
}
