<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceLine extends Model
{
    protected $fillable = [
        'type',
        'quantity',
        'task_id',
        'title',
        'comment',
        'price',
        'invoice_id'
    ];

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

}
