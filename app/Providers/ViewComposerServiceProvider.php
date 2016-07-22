<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ViewComposerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        view()->composer(
            ['users.show'], 'App\Http\ViewComposers\UserHeaderComposer'
        );
        view()->composer(
            ['clients.show'], 'App\Http\ViewComposers\ClientHeaderComposer'
        );
        view()->composer(
            ['tasks.show'], 'App\Http\ViewComposers\TaskHeaderComposer'
        );


    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
