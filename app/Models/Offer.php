<?php

namespace App\Models;

use App\Enums\OfferStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Offer extends Model
{
    use SoftDeletes;
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

    public function setAsWon()
    {
        $this->status = OfferStatus::won()->getStatus();
        $this->save();
    }

    public function setAsLost()
    {
        $this->status = OfferStatus::lost()->getStatus();
        $this->save();
    }
}
