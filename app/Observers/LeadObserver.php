<?php

namespace App\Observers;

use App\Models\Lead;

class LeadObserver
{
    private $relations;

    public function __construct()
    {
        if (app()->environment('testing')) {
            return;
        }
        $this->relations = [
            'comments',
            'activity',
            'appointments',
        ];
    }

    /**
     * Handle the lead "created" event.
     *
     * @return void
     */
    public function created(Lead $lead)
    {
        //
    }

    /**
     * Handle the lead "updated" event.
     *
     * @return void
     */
    public function updated(Lead $lead)
    {
        //
    }

    /**
     * Handle the lead "deleted" event.
     *
     * @return void
     */
    public function deleted(Lead $lead)
    {
        foreach ($this->relations as $relation) {
            $lead->$relation()->delete();
        }
    }

    /**
     * Handle the lead "restored" event.
     *
     * @return void
     */
    public function restored(Lead $lead)
    {
        foreach ($this->relations as $relation) {
            $lead->$relation()->withTrashed()->restore();
        }
    }

    /**
     * Handle the lead "force deleted" event.
     *
     * @return void
     */
    public function forceDeleted(Lead $lead)
    {
        foreach ($this->relations as $relation) {
            $lead->$relation()->withTrashed()->forceDelete();
        }
    }
}
