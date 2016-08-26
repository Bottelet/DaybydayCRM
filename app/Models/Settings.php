<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    protected $fillable = [
        'task_complete_allowed',
        'task_assign_allowed',
        'lead_complete_allowed',
        'lead_assign_allowed'
        ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }
    public function tasks()
    {
        return $this->belongsTo('App\Tasks');
    }
}
