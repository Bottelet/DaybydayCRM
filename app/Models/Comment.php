<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = [
        'description',
        'fk_task_id',
        'fk_user_id'
    ];
    protected $hidden = ['remember_token'];

    public function task()
    {
        return $this->belongsTo(Tasks::class, 'fk_task_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'fk_user_id', 'id');
    }
}
