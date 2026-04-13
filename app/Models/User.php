<?php

namespace App\Models;

use App\Traits\HasExternalId;
use App\Traits\SearchableTrait;
use App\Zizaco\Entrust\Traits\EntrustUserTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Laravel\Cashier\Billable;

class User extends Authenticatable
{
    use Billable;
    use EntrustUserTrait;
    use HasExternalId;
    use HasFactory;
    use Notifiable;
    use SearchableTrait;
    use SoftDeletes;

    protected $searchableFields = ['name', 'email'];

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

    # region Relationships

    public function absences()
    {
        return $this->hasMany(Absence::class);
    }

    public function appointments()
    {
        return $this->morphMany(Appointment::class, 'source');
    }

    public function clients()
    {
        return $this->hasMany(Client::class, 'user_id', 'id');
    }

    public function department()
    {
        return $this->belongsToMany(Department::class);
    }

    public function integrations()
    {
        return $this->hasMany(Integration::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class, 'user_assigned_id', 'id');
    }

    public function settings()
    {
        return $this->hasMany(Setting::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'user_assigned_id', 'id');
    }

    public function userRole()
    {
        return $this->hasOne(RoleUser::class, 'user_id', 'id');
    }

    # endregion

    /*public function tokens()
    {
        return $this->hasMany(Token::class, 'user_id', 'id');
    }*/

    public function canChangePasswordOn(self $user)
    {
        return (bool) ($this->id === $user->id || ($this->roles->first()->name == Role::OWNER_ROLE || $this->roles->first()->name == Role::ADMIN_ROLE));
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
        // dd($this->name, $this->department()->toSql(), $this->department()->getBindings());
        return $this->name . ' ' . '(' . $this->department()->first()->name . ')';
    }

    public function getNameAndDepartmentEagerLoadingAttribute()
    {
        // dd($this->name, $this->department()->toSql(), $this->department()->getBindings());
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
        $keys   = collect();
        $counts = collect();
        foreach ($groups as $groupKey => $group) {
            $keys->push($groupKey);
            $counts->push(count($group));
        }

        return collect(['keys' => $keys, 'counts' => $counts]);
    }

    /**
     * @param $external_id
     *
     * @return mixed
     */
    public function totalOpenAndClosedTasks()
    {
        $groups = $this->tasks()->with('status')->get()->sortBy('status.title')->groupBy('status.title');
        $keys   = collect();
        $counts = collect();
        foreach ($groups as $groupKey => $group) {
            $keys->push($groupKey);
            $counts->push(count($group));
        }

        return collect(['keys' => $keys, 'counts' => $counts]);
    }

    public function displayValue()
    {
        return $this->name;
    }

    public function getSearchableFields(): array
    {
        return $this->searchableFields;
    }

    public function restore()
    {
        $this->restoreA();
        $this->restoreB();
    }
}
