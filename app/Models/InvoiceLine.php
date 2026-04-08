<?php

namespace App\Models;

use App\Repositories\Money\Money;
use App\Repositories\Money\MoneyConverter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class InvoiceLine extends Model
{
    use SoftDeletes;

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
     * Automatically generates a UUID for external_id if not provided.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();
        static::creating(function ($invoiceLine) {
            if (empty($invoiceLine->external_id)) {
                $invoiceLine->external_id = Uuid::uuid4()->toString();
            }
        });
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'external_id';
    }

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
