<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mail extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject',
        'body',
        'template',
        'email',
        'user_id',
        'send_at',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'send_at' => 'datetime',
    ];

    # region Relationships

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    # endregion
}
