<?php
namespace App\Models;

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
        return $this->belongsTo(User::class, 'fk_user_id', 'id');
    }

    public function alltasks()
    {
        return $this->hasMany(Tasks::class, 'fk_client_id', 'id')
        ->orderBy('status', 'asc')
        ->orderBy('created_at', 'desc');
    }
    public function allleads()
    {
        return $this->hasMany(Leads::class, 'fk_client_id', 'id')
        ->orderBy('status', 'asc')
        ->orderBy('created_at', 'desc');
    }

    public function tasks()
    {
        return $this->hasMany(Tasks::class, 'fk_client_id', 'id');
    }
    public function leads()
    {
        return $this->hasMany(Tasks::class, 'fk_client_id', 'id');
    }
    public function documents()
    {
        return $this->hasMany(Document::class, 'fk_client_id', 'id');
    }
    public function invoices()
    {
        return $this->belongsToMany(Invoice::class);
    }
}
