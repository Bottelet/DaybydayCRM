<?php

namespace App\Observers;

use App\Models\Invoice;

class InvoiceObserver
{
    private $relations;

    public function __construct()
    {
        $this->relations = [
            'invoiceLines',
            'payments'
        ];
    }

    /**
     * Handle the task "created" event.
     *
     * @param  \App\Models\Invoice  $task
     * @return void
     */
    public function created(Invoice $task)
    {
        //
    }

    /**
     * Handle the task "updated" event.
     *
     * @param  \App\Models\Invoice  $task
     * @return void
     */
    public function updated(Invoice $task)
    {
    }

    /**
     * Handle the task "deleted" event.
     *
     * @param  \App\Models\Invoice  $task
     * @return void
     */
    public function deleted(Invoice $task)
    {
        foreach ($this->relations as $relation) {
            $task->$relation()->delete();
        }
    }

    /**
     * Handle the task "restored" event.
     *
     * @param  \App\Models\Invoice  $task
     * @return void
     */
    public function restored(Invoice $task)
    {
        foreach ($this->relations as $relation) {
            $task->$relation()->withTrashed()->restore();
        }
    }

    /**
     * Handle the task "force deleted" event.
     *
     * @param  \App\Models\Invoice  $task
     * @return void
     */
    public function forceDeleted(Invoice $task)
    {
        //
    }
}
