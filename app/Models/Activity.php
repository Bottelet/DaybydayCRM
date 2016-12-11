<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'activity_log';
    protected $fillable = [
        'user_id',
        'text',
        'type',
        'type_id',
        'action',
    ];
    protected $guarded = ['id'];

    /**
     * Get the user that the activity belongs to.
     *
     * @return object
     */
    public function task()
    {
        return $this->belongsTo(Tasks::class, 'task_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
