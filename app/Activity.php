<?php

namespace App;

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
        'type_id'
        ];
    protected $guarded = ['id'];

    /**
     * Get the user that the activity belongs to.
     *
     * @return object
     */
    public function task()
    {
        return $this->belongsTo('App\Tasks', 'task_id', 'id');
    }
        public function user()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

    
}
