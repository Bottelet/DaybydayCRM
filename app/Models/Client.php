<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Client extends Model
{
    protected $fillable = [
        'name',
        'vat',
        'primary_email',
        'billing_address1',
        'billing_address2',
        'billing_city',
        'billing_state',
        'billing_zipcode',
        'billing_country',
        'shipping_address1',
        'shipping_address2',
        'shipping_city',
        'shipping_state',
        'shipping_zipcode',
        'shipping_country',
        'primary_number',
        'secondary_number',
        'industry_id',
        'company_type',
        'user_id',
    ];

    public function getFormattedBillingAddressAttribute()
    {
        $address = '';

        if ($this->billing_address1 || $this->billing_city || $this->billing_zipcode) {
            if ($this->billing_address1) {
                $address .= htmlspecialchars($this->billing_address1).'<br/>';
            }
            if ($this->billing_address2) {
                $address .= htmlspecialchars($this->billing_address2).'<br/>';
            }
            if ($this->billing_city || $this->billing_state || $this->billing_zipcode) {
                if ($this->billing_city) {
                    $address .= $this->billing_city.'&nbsp;';
                }
                if ($this->billing_state) {
                    $address .= $this->billing_state.'&nbsp;';
                }
                if ($this->billing_zipcode) {
                    $address .= $this->billing_zipcode;
                }
            }
            if ($this->billing_country) {
                $address .= '<br/>'.$this->billing_country;
            }

            return $address;
        } else {
            return null;
        }
    }

    public function getFormattedShippingAddressAttribute()
    {
        $address = '';

        if ($this->shipping_address1 || $this->shipping_city || $this->shipping_zipcode) {
            if ($this->shipping_address1) {
                $address .= htmlspecialchars($this->shipping_address1).'<br/>';
            }
            if ($this->shipping_address2) {
                $address .= htmlspecialchars($this->shipping_address2).'<br/>';
            }
            if ($this->shipping_city || $this->shipping_state || $this->shipping_zipcode) {
                if ($this->shipping_city) {
                    $address .= $this->shipping_city.'&nbsp;';
                }
                if ($this->shipping_state) {
                    $address .= $this->shipping_state.'&nbsp;';
                }
                if ($this->shipping_zipcode) {
                    $address .= $this->shipping_zipcode;
                }
            }
            if ($this->shipping_country) {
                $address .= '<br/>'.$this->shipping_country;
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

    public function industry()
    {
        return $this->belongsTo(Industry::class, 'industry_id');
    }

    public function getAssignedUserAttribute()
    {
        return User::findOrFail($this->user_id);
    }

    public function scopeMy($query)
    {
        return $query->where('user_id', Auth::id());
    }
}
