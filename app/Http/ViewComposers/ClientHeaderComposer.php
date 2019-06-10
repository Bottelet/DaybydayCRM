<?php

namespace App\Http\ViewComposers;

use App\Models\Client;
use App\Repositories\Client\ClientRepositoryContract;
use Illuminate\View\View;

class ClientHeaderComposer
{
    /**
     * The client repository implementation.
     *
     * @var ClientRepository
     */
    protected $clients;

    /**
     * Create a new profile composer.
     *
     * @param ClientRepository|ClientRepositoryContract $clients
     */
    public function __construct(ClientRepositoryContract $clients)
    {
        $this->clients = $clients;
    }

    /**
     * Bind data to the view.
     *
     * @param View $view
     */
    public function compose(View $view)
    {
        $client  = Client::findOrFail($view->getData()['client']['id']);
        $contact = $client->user;

        $view->with('contact', $contact);
    }
}
