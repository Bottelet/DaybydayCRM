<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon;

class Tasks extends Model
{

    protected $fillable = [
        'title',
        'description',
        'status',
        'fk_user_id_assign',
        'fk_user_id_created',
        'fk_client_id',
        'deadline'
    ];
    protected $dates = ['deadline'];

    protected $hidden = ['remember_token'];

    public function assignee()
    {
        return $this->belongsTo(User::class, 'fk_user_id_assign');
    }

    public function clientAssignee()
    {
        return $this->belongsTo(Client::class, 'fk_client_id');
    }

    public function taskCreator()
    {
        return $this->belongsTo(User::class, 'fk_user_id_created');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'fk_task_id', 'id');
    }

    // create a virtual attribute to return the days until deadline
    public function getDaysUntilDeadlineAttribute()
    {
        return Carbon\Carbon::now()
            ->startOfDay()
            ->diffInDays($this->deadline, false); // if you are past your deadline, the value returned will be negative.
    }

    public function getAssignedUserAttribute()
    {
        return User::findOrFail($this->fk_user_id_assign);
    }

    public function getCreatorUserAttribute()
    {
        return User::findOrFail($this->fk_user_id_assign);
    }

    public function settings()
    {
        return $this->hasMany(Settings::class);
    }

    public function time()
    {
        return $this->hasOne(TaskTime::class, 'fk_task_id', 'id');
    }

    public function allTime()
    {
        return $this->hasMany(TaskTime::class, 'fk_task_id', 'id');
    }

    public function activity()
    {
        return $this->hasMany(Activity::class, 'type_id', 'id')->where('type', 'App\Models\Tasks');
    }
}
