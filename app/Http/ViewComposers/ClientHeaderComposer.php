<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\Repositories\Client\ClientRepositoryContract;

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
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $clients = $this->clients->find($view->getData()['client']['id']);
        /**
         * [User assigned the client]
         * @var contact
         */
        $contact = $clients->user;

        $view->with('contact', $contact);
    }
}
