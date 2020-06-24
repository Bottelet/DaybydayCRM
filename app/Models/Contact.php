<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use  SoftDeletes;

    protected $fillable = [
        'external_id',
        'name',
        'email',
        'primary_number',
        'secondary_number',
        'client_id',
        'is_primary',
        ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
