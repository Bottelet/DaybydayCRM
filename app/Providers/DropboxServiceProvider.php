<?php

namespace App\Providers;

use App\Services\Storage\Dropbox;
use Illuminate\Contracts\Support\DeferrableProvider;
use Spatie\FlysystemDropbox\DropboxAdapter;
use Storage;
use League\Flysystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use Spatie\Dropbox\Client as DropboxClient;
use App\Models\Integration;

class DropboxServiceProvider extends ServiceProvider
{
    protected $api_key;
    private $integration;

    /**
     * Perform post-registration booting of services.
     *
     * @param Integration $integration
     * @return void
     */
    public function boot(Integration $integration)
    {
        return;
        $this->integration = $integration;
        $this->api_key = null;
        $dropbox_integration = $this->integration->whereName(Dropbox::class)->first();
        if ($dropbox_integration) {
            $this->api_key = $dropbox_integration->api_key;
        }

        Storage::extend('dropbox', function ($app, $config) {
            $client = new DropboxClient(
                $this->api_key
            );

            return new Filesystem(new DropboxAdapter($client));
        });
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
