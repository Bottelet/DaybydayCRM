<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\Repositories\invoice\InvoiceRepositoryContract;


class invoiceHeaderComposer
{
    /**
     * The invoice repository implementation.
     *
     * @var invoiceRepository
     */
    protected $invoices;

    /**
     * Create a new profile composer.
     *
     * @param  invoiceRepository  $invoices
     * @return void
     */
    public function __construct(InvoiceRepositoryContract $invoices)
    {
        $this->invoices = $invoices;
    }

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
       
        $invoices = $this->invoices->find($view->getData()['invoice']['id']);    

        $client = $invoices->clients->first();
        
        $view->with('client', $client);

    }
}