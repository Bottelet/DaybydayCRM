<?php

namespace App\Models;

use App\Repositories\Money\Money;

use App\Services\Invoice\InvoiceCalculator;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property Integer amount
 */
class Payment extends Model
{
    use  SoftDeletes;

    protected $fillable = [
        'invoice_id',
        'external_id',
        'description',
        'amount',
        'payment_source',
        'payment_date',
        'integration_payment_id',
        'integration_type',
    ];

    protected $dates = ['payment_date'];


    public static function getByExternalId($externalId)
    {
        return self::where('external_id', $externalId)->first();
    }


    /**
     * Mutator pour setter l'amount
     *
     * @param float $amount
     * @throws Exception
     */
    public function setAmountAttribute($amount)
    {
        if($amount < 0){
            throw new Exception("Amount must be greater than 0.");
        }
        $invoice = Invoice::find($this->invoice_id);

        if (!$invoice) {
            throw new Exception("Invoice not found.");
        }
        $invoicecalculator = new InvoiceCalculator($invoice);

        $amountmoney=$invoicecalculator->getAmountDue();
        $amountvalue=$amountmoney->getAmount();
        $realamount=$amount;


        if(isset($this->amount)){
            $amountvalue+=$this->amount;
        }


        if($realamount > $amountvalue){
            throw  new Exception(" Amount exceeds due amount .Remaining amount to be paid:  ".$amountvalue/100 ." ".$amountmoney->getCurrency()->getCode());
        }

        $this->attributes['amount'] = $realamount;

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

    public function getPriceAttribute()
    {
        return app(Money::class, ['amount' => $this->amount]);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
