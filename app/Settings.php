<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class settings extends Model
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
    public function Tasks()
    {
    	return $this->belongsTo('App\Tasks');
    }
    public function permissions()
    {
        # code...
    }

}
