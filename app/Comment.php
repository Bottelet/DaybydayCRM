<?php
namespace App;

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
        return $this->belongsTo('App\Tasks', 'fk_task_id', 'id');
    }
    public function user()
    {
        return $this->belongsTo('App\User', 'fk_user_id', 'id');
    }
}
