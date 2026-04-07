<?php
namespace App\Models;

use http\Exception\InvalidArgumentException;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable =
    [
        'name',
        'external_id',
        'description',
    ];


    public function setNameAttribute($value)
    {
        if(!is_string($value)){
           throw new InvalidArgumentException('The name of department must be a string');
        }
        $this->attributes['name'] = $value;
    }



    protected $hidden = ['pivot'];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
