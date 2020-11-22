<?php

namespace App\Providers;

use App\Models\Integration;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Task;
use App\Models\Lead;
use App\Models\Project;
use App\Observers\ClientObserver;
use App\Observers\TaskObserver;
use App\Observers\LeadObserver;
use App\Observers\ProjectObserver;
use App\Observers\InvoiceObserver;
use App\Repositories\Format\GetDateFormat;
use Illuminate\Support\ServiceProvider;
use Laravel\Dusk\DuskServiceProvider;
use Laravel\Cashier\Cashier;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Cashier::ignoreMigrations();
        Client::observe(ClientObserver::class);
        Task::observe(TaskObserver::class);
        Lead::observe(LeadObserver::class);
        Project::observe(ProjectObserver::class);
        Invoice::observe(InvoiceObserver::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment('local', 'testing')) {
            $this->app->register(DuskServiceProvider::class);
        }
        $this->app->singleton(GetDateFormat::class);
    }
}
