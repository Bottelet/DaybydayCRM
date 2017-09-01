<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'sent_at',
        'payment_received_at',
        'status',
        'sent_at',
        'due_at',
        'client_id',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function invoiceLines()
    {
        return $this->hasMany(InvoiceLine::class);
    }

    public function canUpdateInvoice()
    {
        if ($this->sent_at != null) {
            return false;
        }
        return true;
    }
    
}
