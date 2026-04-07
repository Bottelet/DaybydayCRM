<?php

namespace App\Models;

use App\Repositories\Money\Money;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $appends = ['divided_price'];
    protected $hidden=['id'];
    
    public function getRouteKeyName()
    {
        return 'external_id';
    }

    public function setPriceAttribute($value)
    {
        echo "miset prixproduit ah io a!";
        if($value<0){
            throw new \Exception("Product price must be greater than 0");
        }
        $this->attributes['price'] = $value;
    }
    public  static  function findByName($name){
        return self::where('name', $name)->first();
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
