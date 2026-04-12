<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Repositories\Money\Money;
use App\Traits\HasExternalId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int amount
 */
class Payment extends Model
{
    use HasExternalId;
    use HasFactory;
    use SoftDeletes;

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

    //region Relationships

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    //endregion

    public function getPriceAttribute()
    {
        return app(Money::class, ['amount' => $this->amount]);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
