<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasExternalId;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

class Absence extends Model
{
    use HasExternalId;
    use HasFactory;

    protected $fillable = [
        'external_id',
        'reason',
        'start_at',
        'end_at',
        'user_id',
        'comment',

    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    protected $hidden = ['id', 'user_id'];

    // getRouteKeyName() is provided by HasExternalId trait

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
