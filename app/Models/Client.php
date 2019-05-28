<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'company_name',
        'vat',
        'email',
        'address',
        'zipcode',
        'city',
        'primary_number',
        'secondary_number',
        'primary_contact_name',
        'industry_id',
        'company_type',
        'user_id',
    ];

    public function getHtmlFormattedAddressAttribute()
    {
        $address = '';

        if ($this->address || $this->city || $this->zipcode) {
            if ($this->address) {
                $address .= htmlspecialchars($this->address).'<br/>';
            }
            if ($this->city || $this->zipcode) {
                if ($this->city) {
                    $address .= $this->city.'&nbsp;';
                }
                if ($this->zipcode) {
                    $address .= $this->zipcode;
                }
            }

            return $address;
        } else {
            return null;
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'client_id', 'id')
            ->orderBy('status', 'asc')
            ->orderBy('created_at', 'desc');
    }

    public function leads()
    {
        return $this->hasMany(Lead::class, 'client_id', 'id')
            ->orderBy('status', 'asc')
            ->orderBy('created_at', 'desc');
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class, 'client_id', 'id')
            ->orderBy('name', 'asc')
            ->orderBy('created_at', 'desc');
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'client_id', 'id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function getAssignedUserAttribute()
    {
        return User::findOrFail($this->user_id);
    }
}
