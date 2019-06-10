<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = [
        'name',
        'job_title',
        'email',
        'address1',
        'address2',
        'city',
        'state',
        'zipcode',
        'country',
        'primary_number',
        'secondary_number',
        'client_id',
    ];

    public function getFormattedAddressAttribute()
    {
        $address = '';

        if ($this->address1 || $this->city || $this->zipcode) {
            if ($this->address1) {
                $address .= htmlspecialchars($this->address1).'<br/>';
            }
            if ($this->address2) {
                $address .= htmlspecialchars($this->address2).'<br/>';
            }
            if ($this->city || $this->state || $this->zipcode) {
                if ($this->city) {
                    $address .= $this->city.'&nbsp;';
                }
                if ($this->state) {
                    $address .= $this->state.'&nbsp;';
                }
                if ($this->zipcode) {
                    $address .= $this->zipcode;
                }
            }
            if ($this->country) {
                $address .= '<br/>'.$this->country;
            }

            return $address;
        } else {
            return null;
        }
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}
