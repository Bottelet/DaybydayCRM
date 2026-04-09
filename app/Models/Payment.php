<?php

namespace App\Models;

use App\Repositories\Money\Money;
use App\Traits\HasExternalId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int amount
 */
class Payment extends Model
{
    use SoftDeletes, HasExternalId;

    protected $fillable = [
        'external_id',
        'description',
        'amount',
        'payment_source',
        'payment_date',
        'integration_payment_id',
        'integration_type',
        'invoice_id',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'deleted_at' => 'datetime',
    ];

    // getRouteKeyName() is provided by HasExternalId trait

    public function getPriceAttribute()
    {
        return app(Money::class, ['amount' => $this->amount]);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
