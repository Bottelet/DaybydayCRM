<?php

namespace App\Models;

use App\Traits\HasExternalId;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasExternalId;

    protected $fillable =
        [
            'name',
            'external_id',
            'description',
        ];

    protected $hidden = ['pivot'];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
