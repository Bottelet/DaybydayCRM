<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'client_number',
        'invoice_number',
        'company',
        'country',
        'currency',
        'vat',
        'language',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tasks()
    {
        return $this->belongsTo(Task::class);
    }
}
