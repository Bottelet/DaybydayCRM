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
        'App\Events\ClientAction' => [
            'App\Listeners\ClientActionNotify',
            'App\Listeners\ClientActionLog',
        ],
         'App\Events\TaskAction' => [
            'App\Listeners\TaskActionNotify',
            'App\Listeners\TaskActionLog',
         ],
        'App\Events\LeadAction' => [
            'App\Listeners\LeadActionNotify',
            'App\Listeners\LeadActionLog',
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
