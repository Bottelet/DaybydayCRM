<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{

    protected $fillable = [
            'name',
            'company_name',
            'vat',
            'email',
            'address',
            'zipcode',
            'city',
            'primary_number',
            'secondary_number',
            'industry_id',
            'company_type',
            'fk_user_id'];

    public function userAssignee()
    {
        return $this->belongsTo('App\User', 'fk_user_id', 'id');
    }

    public function alltasks()
    {
        return $this->hasMany('App\Tasks', 'fk_client_id', 'id')
        ->orderBy('status', 'asc')
        ->orderBy('created_at', 'desc');
    }
    public function allleads()
    {
        return $this->hasMany('App\Leads', 'fk_client_id', 'id')
        ->orderBy('status', 'asc')
        ->orderBy('created_at', 'desc');
    }

    public function tasks()
    {
        return $this->hasMany('App\Tasks', 'fk_client_id', 'id');
    }
    public function leads()
    {
        return $this->hasMany('App\Leads', 'fk_client_id', 'id');
    }
    public function documents()
    {
        return $this->hasMany('App\Document', 'fk_client_id', 'id');
    }
    public function invoices()
    {
        return $this->belongsToMany('App\Invoice');
    }

}
