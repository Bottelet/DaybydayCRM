<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessHour extends Model
{
    protected $fillable = [
        'settings_id',
        'open_time',
        'close_time',
        'day',
    ];
}
