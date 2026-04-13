<?php

namespace App\Models;

use App\Traits\HasExternalId;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use HasExternalId;
    use HasFactory;
    use SoftDeletes;

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

    protected $casts = [
        'start_at'   => 'datetime',
        'end_at'     => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $hidden = ['id', 'user_id', 'source_type', 'source_id', 'client_id'];

    # region Relationships

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function boot()
    {
        parent::boot();
        // HasExternalId trait handles external_id generation
    }

    // getRouteKeyName() is provided by HasExternalId trait

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d\TH:i:s.000000\Z');
    }

    # endregion
}
