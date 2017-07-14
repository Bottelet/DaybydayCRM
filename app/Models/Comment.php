<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = [
        'description',
        'task_id',
        'user_id'
    ];
    protected $hidden = ['remember_token'];

    /**
     * Get all of the owning commentable models.
     */
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

    public function mentionedUsers()
    {
        preg_match_all('/@([\w\-]+)/', $this->description, $matches);
 
        return $matches[1];
    }

    public function setDescriptionAttribute($description)
    {
        $this->attributes['description'] = preg_replace(
          '/@([\w\-]+)/',
          '<a href="/profiles/$1">$0</a>',
          $description
      );
 
    }
}
