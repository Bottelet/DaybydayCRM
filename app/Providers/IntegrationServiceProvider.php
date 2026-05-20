<?php

namespace App\Providers;

use App\Services\Billing\BillingIntegrationRegistry;
use App\Services\Storage\StorageAdapterRegistry;
use Illuminate\Support\ServiceProvider;

/**
 * Binds integration-related services into the container so that controllers,
 * services and observers receive them via dependency injection instead of
 * reaching for static helpers or the model.
 */
class IntegrationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Both registries are singletons so that the adapter is resolved at
        // most once per request (they cache the resolved driver internally).
        $this->app->singleton(BillingIntegrationRegistry::class);
        $this->app->singleton(StorageAdapterRegistry::class);
    }

    public function boot(): void
    {
        // Nothing to boot – adapters are resolved lazily on first call to
        // driver() so they never fire during service-provider boot, which
        // would risk hitting the database during migrations / artisan calls.
    }
}
