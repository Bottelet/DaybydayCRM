<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = [
        'name',
        'job_title',
        'email',
        'address',
        'zipcode',
        'city',
        'primary_number',
        'secondary_number',
        'client_id',
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

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}
