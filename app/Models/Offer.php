<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $fillable = [
        'sent_at',
        'status',
        'due_at',
        'client_id',
        'source_id',
        'source_type',
        'status',
        'external_id'
    ];

    public function getRouteKeyName()
    {
        return 'external_id';
    }

    public function invoiceLines()
    {
        return $this->hasMany(InvoiceLine::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }
}
