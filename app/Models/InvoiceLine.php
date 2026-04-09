<?php

namespace App\Models;

use App\Repositories\Money\Money;
use App\Repositories\Money\MoneyConverter;
use App\Traits\HasExternalId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceLine extends Model
{
    use HasExternalId, SoftDeletes;

    protected $fillable = [
        'external_id',
        'type',
        'quantity',
        'title',
        'comment',
        'price',
        'invoice_id',
        'product_id',
        'offer_id',
    ];

    /**
     * Bootstrap the model and its traits.
     * HasExternalId trait automatically generates a UUID for external_id if not provided.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();
        // HasExternalId trait handles external_id generation
    }

    // getRouteKeyName() is provided by HasExternalId trait

    public function tasks()
    {
        return $this->belongsTo(Task::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function task()
    {
        return $this->invoice->task;
    }

    public function getTotalValueAttribute()
    {
        return $this->quantity * $this->price;
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getTotalValueConvertedAttribute()
    {
        $money = new Money($this->quantity * $this->price);

        return app(MoneyConverter::class, ['money' => $money])->format();
    }

    public function getPriceConvertedAttribute()
    {
        $money = new Money($this->price);

        return app(MoneyConverter::class, ['money' => $money])->format();
    }
}
