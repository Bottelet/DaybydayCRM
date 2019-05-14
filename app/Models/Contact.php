<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = [
        'name',
        'job_title',
        'email',
        'address',
        'zipcode',
        'city',
        'primary_number',
        'secondary_number',
        'client_id',
    ];
}
