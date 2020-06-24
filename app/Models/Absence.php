<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absence extends Model
{
    protected $fillable = [
        'external_id',
        'reason',
        'start_at',
        'end_at',
        'user_id',
        'comment',

    ];

    protected $dates = ['start_at', 'end_at'];

    protected $hidden = ['id', 'user_id'];

    public function getRouteKeyName()
    {
        return 'external_id';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
