<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mail extends Model
{
    protected $fillable = [
        'subject',
        'body',
        'template',
        'email',
        'user_id',
        'send_at',
        'sent_at'
    ];

    protected $dates = ['sent_at', 'send_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
