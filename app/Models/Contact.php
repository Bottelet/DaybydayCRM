<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = [
        'name',
        'email',
        'address',
        'zipcode',
        'city',
        'primary_number',
        'cellular_number',
        'client_id',
    ];
}
