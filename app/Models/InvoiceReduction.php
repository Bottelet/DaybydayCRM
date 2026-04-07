<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceReduction extends Model
{
    protected $table = 'invoice_reduction';

    public function reduction()
    {
        return $this->hasOne(InvoiceReduction::class);
    }
}
