<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'description',
        'task_id',
        'user_id',
    ];

    protected $hidden = ['remember_token'];

    // region Relationships

    public function commentable()
    {
        return $this->morphTo('source');
    }

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    // endregion

    public function mentionedUsers()
    {
        preg_match_all('/@([\w\-]+)/', $this->description, $matches);

        return $matches[1];
    }
}
