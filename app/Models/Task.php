<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon;

class Task extends Model
{

    protected $fillable = [
        'title',
        'description',
        'status',
        'user_assigned_id',
        'user_created_id',
        'client_id',
        'deadline'
    ];
    protected $dates = ['deadline'];

    protected $hidden = ['remember_token'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_assigned_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_created_id');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'source');
    }
    
    public function getDaysUntilDeadlineAttribute()
    {
        return Carbon\Carbon::now()
            ->startOfDay()
            ->diffInDays($this->deadline, false); // if you are past your deadline, the value returned will be negative.
    }

    public function getAssignedUserAttribute()
    {
        return User::findOrFail($this->user_assigned_id);
    }

    public function getCreatorUserAttribute()
    {
        return User::findOrFail($this->user_assigned_id);
    }

    public function activity()
    {
        return $this->morphMany(Activity::class, 'source');
    }

    public function canUpdateInvoice()
    {
        //If there is no invoice, it should be possible, because it also creates
        if (!$this->invoice) {
            return true;
        }
        return $this->invoice->canUpdateInvoice();
    }

    /**
     * Add a reply to the thread.
     *
     * @param  array $reply
     * @return Model
     */
    public function addComment($reply)
    {
        $reply = $this->comments()->create($reply);
        return $reply;
    }
}
