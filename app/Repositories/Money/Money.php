<?php
namespace  App\Repositories\Money;

use App\Models\Setting;
use App\Repositories\Currency\Currency;

class Money
{
    /**
     * @var int
     */
    private $amount;
    /**
     * @var Currency
     */
    private $currency;

    public function __construct($amount = 0)
    {
        $currency = Setting::select('currency')->first()->currency;
        $this->amount = $amount;
        $this->currency = new Currency($currency);
    }
    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return Currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    public function getBigDecimalAmount()
    {
        return $this->getAmount() / 100;
    }
}
