<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon;

class Leads extends Model
{
		protected $fillable = [
        'title',
        'note',
        'status',
        'fk_user_id_assign',
        'fk_user_id_created',
        'fk_client_id',
        'contact_date',
        
    ];
	 protected $dates = ['contact_date'];

    protected $hidden = ['remember_token'];
    
    public function assignee()
    {
    	return $this->belongsTo('App\User', 'fk_user_id_assign');
    }
    public function createdBy()
    {
    	return $this->belongsTo('App\User', 'fk_user_id_created');
    }
    public function clientAssignee()
    {
    	return $this->belongsTo('App\Client', 'fk_client_id');
    }
    public function notes()
    {
    	return $this->hasMany('App\Note', 'fk_lead_id', 'id');
    }
        // create a virtual attribute to return the days until deadline
    public function getDaysUntilContactAttribute()
    {   
        return Carbon\Carbon::now()->startOfDay()->diffInDays($this->contact_date, false); 
     }

}
