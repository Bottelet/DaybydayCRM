<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon;

class Lead extends Model
{
    protected $fillable = [
        'title',
        'note',
        'status',
        'user_assigned_id',
        'user_created_id',
        'client_id',
        'contact_date',

    ];
    protected $dates = ['contact_date'];

    protected $hidden = ['remember_token'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_assigned_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_created_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function notes()
    {
        return $this->hasMany(Note::class, 'lead_id', 'id');
    }

    public function activity()
    {
        return $this->morphMany(Activity::class, 'source');
    }

    public function getDaysUntilContactAttribute()
    {
        return Carbon\Carbon::now()->startOfDay()->diffInDays($this->contact_date, false);
    }
}
