<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'received',
        'sent',
        'payment_date'
    ];
    public function clients()
    {
        return $this->belongsToMany('App\Client');
    }
    public function tasktime()
    {
        return $this->belongsToMany('App\TaskTime')
       ;
    }
}
