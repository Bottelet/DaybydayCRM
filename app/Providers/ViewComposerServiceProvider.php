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
            ['users.show'],
            'App\Http\ViewComposers\UserHeaderComposer'
        );
        view()->composer(
            ['clients.show'],
            'App\Http\ViewComposers\ClientHeaderComposer'
        );
        view()->composer(
            ['tasks.show'],
            'App\Http\ViewComposers\TaskHeaderComposer'
        );
        view()->composer(
            ['projects.show'],
            'App\Http\ViewComposers\ProjectHeaderComposer'
        );
        view()->composer(
            ['leads.show'],
            'App\Http\ViewComposers\LeadHeaderComposer'
        );
        view()->composer(
            ['invoices.show'],
            'App\Http\ViewComposers\InvoiceHeaderComposer'
        );

        view()->composer(
            [
                'departments.index',
                'absence.index',
                'clients.index',
                'clients.tabs.invoicetab',
                'clients.tabs.leadtab',
                'clients.tabs.projectstab',
                'clients.tabs.tasktab',
                'datatables.index',
                'invoices._paymentList',
                'projects.index',
                'roles.index',
                'tasks.index',
                'users.index',
                'users.show'
            ],
            'App\Http\ViewComposers\DataTableLanguageComposer'
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
