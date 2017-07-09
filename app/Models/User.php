<?php
namespace App\Models;

use Fenos\Notifynder\Notifable;
use Illuminate\Notifications\Notifiable;
use Cache;
use Zizaco\Entrust\Traits\EntrustUserTrait;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\Setting;

class User extends Authenticatable
{
    use Notifiable, EntrustUserTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'email', 'password', 'address', 'personal_number', 'work_number', 'image_path'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $dates = ['trial_ends_at', 'subscription_ends_at'];
    protected $hidden = ['password', 'password_confirmation', 'remember_token'];


    protected $primaryKey = 'id';

    public function tasks()
    {
        return $this->hasMany(Task::class, 'user_assigned_id', 'id');
    }

    public function leads()
    {
        return $this->hasMany(Lead::class, 'user_id', 'id');
    }
    
    public function department()
    {
        return $this->belongsToMany(Department::class, 'department_user')->withPivot('department_id');
    }

    public function userRole()
    {
        return $this->hasOne(RoleUser::class, 'user_id', 'id');
    }

    public function isOnline()
    {
        return Cache::has('user-is-online-' . $this->id);
    }

    public function getNameAndDepartmentAttribute()
    {
        return $this->name . ' ' . '(' . $this->department()->first()->name . ')';
    }
    
    public function moveTasks($user_id)
    {
        $tasks = $this->tasks()->get();
        foreach ($tasks as $task) {
            $task->user_assigned_id = $user_id;
            $task->save();
        }
    }

    public function moveLeads($user_id)
    {
        $leads = $this->leads()->get();
        foreach ($leads as $lead) {
            $lead->user_assigned_id = $user_id;
            $lead->save();
        }
    }

    public function moveClients($user_id)
    {
        $clients = $this->clients()->get();
        foreach ($clients as $client) {
            $client->user_id = $user_id;
            $client->save();
        }
    }

    public function getAvatarattribute()
    {
        $setting = Setting::first();
        return $this->image_path ? 'images/' . $setting->company . '/' . $this->image_path : 'images/default_avatar.jpg';
    }
}
