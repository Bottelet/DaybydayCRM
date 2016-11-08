<?php

namespace App\Providers;

use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\ClientCreate' => [
            'App\Listeners\ClientCreateNotify',
            'App\Listeners\ClientCreateLog',
        ],
         'App\Events\TaskAction' => [
            'App\Listeners\TaskActionNotify',
            'App\Listeners\TaskActionLog',
        ],
        'App\Events\LeadCreate' => [
            'App\Listeners\LeadCreateNotify',
            'App\Listeners\LeadCreateLog',
        ],
    ];

    /**
     * Register any other events for your application.
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
