<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskTime extends Model
{
    protected $fillable = [
        'time',
        'overtime',
        'task_id',
        'title',
        'comment',
        'value'
    ];

    protected $hidden = ['remember_token'];

    protected $table = 'tasks_time';

    public function tasks()
    {
        return $this->belongsTo(Task::class);
    }

    public function invoices()
    {
        return $this->belongsToMany(Invoice::class);
    }
}
