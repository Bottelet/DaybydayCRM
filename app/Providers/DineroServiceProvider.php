<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class DineroServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->app->bind('dinero', 'App\Dinero');
    }
}
