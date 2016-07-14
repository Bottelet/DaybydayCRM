<?php
namespace App;

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
        return $this->belongsTo('App\User', 'fk_user_id_assign');
    }

    public function clientAssignee()
    {
        return $this->belongsTo('App\Client', 'fk_client_id');
    }
    
    public function taskCreator()
    {
        return $this->belongsTo('App\User', 'fk_user_id_created');
    }

    public function comments()
    {
        return $this->hasMany('App\Comment', 'fk_task_id', 'id');
    }

    // create a virtual attribute to return the days until deadline
    public function getDaysUntilDeadlineAttribute()
    {
        return Carbon\Carbon::now()
        ->startOfDay()
        ->diffInDays($this->deadline, false); // if you are past your deadline, the value returned will be negative.
    }
    public function settings()
    {
        return $this->hasMany('App\Settings');
    }
    public function time()
    {
        return $this->hasOne('App\TaskTime', 'fk_task_id', 'id');
    }
    public function activity()
    {
        return $this->hasMany('App\Activity', 'type_id', 'id')->where('type', 'task');
    }
}
