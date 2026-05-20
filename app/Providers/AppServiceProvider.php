<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\Document;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Task;
use App\Observers\ClientObserver;
use App\Observers\DocumentObserver;
use App\Observers\InvoiceObserver;
use App\Observers\LeadObserver;
use App\Observers\ProjectObserver;
use App\Observers\TaskObserver;
use App\Repositories\Format\GetDateFormat;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Client::observe(ClientObserver::class);
        Task::observe(TaskObserver::class);
        Lead::observe(LeadObserver::class);
        Project::observe(ProjectObserver::class);
        Invoice::observe(InvoiceObserver::class);
        Document::observe(DocumentObserver::class);

        // Force URL generation to respect APP_URL configuration.
        // In testing, this remains disabled by default to avoid polluting the
        // URL generator with production/staging values, but it can be enabled
        // explicitly for tests that need absolute URLs to honor APP_URL.
        $forceUrlInTesting = (bool) config('app.force_url_in_testing', env('APP_FORCE_URL_IN_TESTING', false));

        if (($appUrl = config('app.url')) && ( ! $this->app->environment('testing') || $forceUrlInTesting)) {
            URL::forceRootUrl($appUrl);

            if ($scheme = parse_url($appUrl, PHP_URL_SCHEME)) {
                URL::forceScheme($scheme);
            }
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment('local', 'testing')) {
            if (class_exists(\Laravel\Dusk\DuskServiceProvider::class)) {
                $this->app->register(\Laravel\Dusk\DuskServiceProvider::class);
            }
        }
        if ($this->app->environment('local') && class_exists(\Laravel\Tinker\TinkerServiceProvider::class)) {
            $this->app->register(\Laravel\Tinker\TinkerServiceProvider::class);
        }
        $this->app->singleton(GetDateFormat::class);
    }
}
