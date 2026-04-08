<?php

namespace App\Models;

use App\Repositories\Money\Money;
use App\Traits\HasExternalId;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasExternalId;
    protected $appends = ['divided_price'];

    protected $hidden = ['id'];

    public function getRouteKeyName()
    {
        return 'external_id';
    }

    public function getMoneyPriceAttribute()
    {
        $money = new Money($this->price);

        return $money;
    }

    public function getDividedPriceAttribute()
    {
        return $this->price / 100;
    }
}
