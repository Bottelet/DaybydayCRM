<?php

namespace App\Observers;

use App\Models\Client;

class ClientObserver
{
    private $relations;

    public function __construct()
    {
        if (app()->environment('testing')) {
            return;
        }
        $this->relations = [
            'tasks',
            'leads',
            'documents',
            'projects',
            'invoices',
            'contacts',
            'appointments',
        ];
    }

    /**
     * Handle the client "created" event.
     *
     * @return void
     */
    public function created(Client $client)
    {
        //
    }

    /**
     * Handle the client "updated" event.
     *
     * @return void
     */
    public function updated(Client $client)
    {
        //
    }

    /**
     * Handle the client "deleted" event.
     *
     * @return void
     */
    public function deleted(Client $client)
    {
        foreach ($this->relations as $relation) {
            $client->$relation()->delete();
        }
    }

    /**
     * Handle the client "restored" event.
     *
     * @return void
     */
    public function restored(Client $client)
    {
        foreach ($this->relations as $relation) {
            $client->$relation()->withTrashed()->restore();
        }
    }

    /**
     * Handle the client "force deleted" event.
     *
     * @return void
     */
    public function forceDeleted(Client $client)
    {
        //
    }
}
