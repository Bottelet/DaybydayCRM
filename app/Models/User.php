<?php
namespace App\Models;

use Fenos\Notifynder\Notifable;
use Illuminate\Notifications\Notifiable;
use Cache;
use App\Models\Client;
use App\Zizaco\Entrust\Traits\EntrustUserTrait;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\Setting;
use App\Api\v1\Models\Token;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Laravel\Cashier\Billable;
use Carbon\Carbon;

class User extends Authenticatable
{
    use Notifiable, EntrustUserTrait,  SoftDeletes, Billable;

    public function restore()
    {
        $this->restoreA();
        $this->restoreB();
    }

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
    protected $fillable = [
        'external_id',
        'name',
        'email',
        'password',
        'address',
        'primary_number',
        'secondary_number',
        'image_path',
        'language',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['id', 'password', 'password_confirmation', 'remember_token', 'image_path'];
    protected $appends = ['avatar'];

    protected $primaryKey = 'id';

    public function tasks()
    {
        return $this->hasMany(Task::class, 'user_assigned_id', 'id');
    }

    public function leads()
    {
        return $this->hasMany(Lead::class, 'user_assigned_id', 'id');
    }

    public function clients()
    {
        return $this->hasMany(Client::class, 'user_id', 'id');
    }

    public function department()
    {
        return $this->belongsToMany(Department::class);
    }

    public function userRole()
    {
        return $this->hasOne(RoleUser::class, 'user_id', 'id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function absences()
    {
        return $this->hasMany(Absence::class);
    }

    public function tokens()
    {
        return $this->hasMany(Token::class, 'user_id', 'id');
    }

    public function canChangePasswordOn(User $user)
    {
        if($this->id === $user->id || ( $this->roles->first()->name == Role::OWNER_ROLE || $this->roles->first()->name == Role::ADMIN_ROLE)) {
            return true;
        }

        return false;
    }

    public function canChangeRole()
    {
        return $this->roles->first()->name == Role::OWNER_ROLE || $this->roles->first()->name == Role::ADMIN_ROLE;
    }


    public function isOnline()
    {
        return Cache::has('user-is-online-' . $this->id);
    }

    public function getNameAndDepartmentAttribute()
    {
        //dd($this->name, $this->department()->toSql(), $this->department()->getBindings());
        return $this->name . ' ' . '(' . $this->department()->first()->name . ')';
    }


    public function getNameAndDepartmentEagerLoadingAttribute()
    {
        //dd($this->name, $this->department()->toSql(), $this->department()->getBindings());
        return $this->name . ' ' . '(' . $this->relations['department'][0]->name . ')';
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
        $image_path = $this->image_path ? Storage::url($this->image_path) : '/images/default_avatar.jpg';
        return $image_path;
    }

    public function totalOpenAndClosedLeads()
    {
        $groups = $this->leads()->with('status')->get()->sortBy('status.title')->groupBy('status.title');
        $keys = collect();
        $counts = collect();
        foreach ($groups as $groupKey => $group) {
            $keys->push($groupKey);
            $counts->push(count($group));
        }

        return collect(['keys' => $keys, 'counts' => $counts]);
    }

    /**
     * @param $external_id
     * @return mixed
     */
    public function totalOpenAndClosedTasks()
    {
        $groups = $this->tasks()->with('status')->get()->sortBy('status.title')->groupBy('status.title');
        $keys = collect();
        $counts = collect();
        foreach ($groups as $groupKey => $group) {
            $keys->push($groupKey);
            $counts->push(count($group));
        }

        return collect(['keys' => $keys, 'counts' => $counts]);
    }
}
