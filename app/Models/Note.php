<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    protected $fillable = [
    'note',
    'status',
    'fk_lead_id',
    'fk_user_id'
    ];
    protected $hidden = ['remember_token'];

    public function lead()
    {
        return $this->belongsTo('App\Leads', 'fk_lead_id', 'id');
    }
    public function user()
    {
        return $this->belongsTo('App\User', 'fk_user_id', 'id');
    }
}
