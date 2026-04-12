<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enums\OfferStatus;
use App\Traits\HasExternalId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Offer extends Model
{
    use HasExternalId;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'sent_at',
        'status',
        'status_id',
        'due_at',
        'client_id',
        'source_id',
        'source_type',
        'external_id',
    ];

    // getRouteKeyName() is provided by HasExternalId trait

    # region Relationships

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function invoiceLines()
    {
        return $this->hasMany(InvoiceLine::class);
    }

    public function lead()
    {
        return $this->source();
    }

    public function lines()
    {
        return $this->invoiceLines();
    }

    public function source()
    {
        return $this->morphTo();
    }

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    # endregion

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
