<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskTime extends Model
{
    protected $fillable = [
        'time',
        'overtime',
        'fk_task_id',
        'title',
        'comment',
        'value'
    ];

    protected $hidden = ['remember_token'];

    protected $table = 'tasks_time';
    public function tasks()
    {
        return $this->belongsTo('App\Tasks');
    }
    public function invoices()
    {
        return $this->belongsToMany('App\Invoice');
    }
}
