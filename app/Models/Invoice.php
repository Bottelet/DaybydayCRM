<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Services\Invoice\InvoiceCalculator;
use App\Traits\HasExternalId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property mixed sent_at
 * @property string integration_invoice_id
 * @property string integration_type
 * @property mixed reference
 */
class Invoice extends Model
{
    use HasExternalId;
    use HasFactory;
    use SoftDeletes;

    public const STATUS_SENT = 'sent';

    protected $fillable = [
        'status',
        'sent_at',
        'due_at',
        'client_id',
        'user_created_id',
        'integration_invoice_id',
        'integration_type',
        'source_id',
        'source_type',
        'external_id',
        'offer_id',
    ];

    protected $casts = [
        'due_at'     => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // getRouteKeyName() is provided by HasExternalId trait

    # region Relationships

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_created_id');
    }

    public function invoiceLines()
    {
        return $this->hasMany(InvoiceLine::class);
    }

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'invoice_id', 'id');
    }

    public function source()
    {
        return $this->morphTo('source');
    }

    # endregion

    public function canUpdateInvoice(): bool
    {
        return ! $this->isSent();
    }

    public function isSent(): bool
    {
        return $this->sent_at !== null;
    }

    public function removeReference(): bool
    {
        return $this->update([
            'integration_invoice_id' => null,
            'integration_type'       => null,
            'source_id'              => null,
            'source_type'            => null,
        ]);
    }

    public function scopePastDueAt($query)
    {
        return $query->where('due_at', '<', now())->NotFullyPaid();
    }

    public function scopeNotFullyPaid($query)
    {
        return $query->where('status', '!=', InvoiceStatus::paid()->getStatus());
    }

    public function getTotalPriceAttribute()
    {
        return (new InvoiceCalculator($this))->getTotalPrice();
    }
}
