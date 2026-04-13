<?php

namespace App\Models;

use App\Repositories\Money\Money;
use App\Traits\HasExternalId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasExternalId;
    use HasFactory;

    protected $appends = ['divided_price'];

    protected $hidden = ['id'];

    // getRouteKeyName() is provided by HasExternalId trait

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
