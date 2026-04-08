<?php

namespace App\Models;

use App\Traits\HasExternalId;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use SoftDeletes, HasExternalId;

    protected static function boot()
    {
        parent::boot();
        // HasExternalId trait handles external_id generation
    }

    protected $fillable = [
        'user_id',
        'source_id',
        'source_type',
        'start_at',
        'end_at',
        'external_id',
        'title',
        'description',
        'color',
        'client_id',
    ];

    protected $dates = ['start_at', 'end_at'];

    protected $hidden = ['id', 'user_id', 'source_type', 'source_id', 'client_id'];

    // getRouteKeyName() is provided by HasExternalId trait

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
