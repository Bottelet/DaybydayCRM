<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use App\Repositories\Invoice\InvoiceRepositoryContract;
use App\Repositories\Client\ClientRepositoryContract;
use App\Models\Integration;

class InvoicesController extends Controller
{

    protected $clients;
    protected $invoices;

    public function __construct(
        InvoiceRepositoryContract $invoices,
        ClientRepositoryContract $clients
    )
    {
        $this->invoices = $invoices;
        $this->clients = $clients;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('invoices.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->invoices->create('test');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $integrationCheck = Integration::first();
        $invoice = $this->invoices->find($id);
        if ($integrationCheck) {
            $api = Integration::getApi('billing');
            $apiConnected = true;
            $invoiceContacts = $api->getContacts($invoice->client->email);
            // If we can't find a client in the integration, show all
            if (!$invoiceContacts) {
                $invoiceContacts = $api->getContacts();
            }
            
        } else {
            $apiConnected = false;
            $invoiceContacts = [];
        }

        return view('invoices.show')
            ->withInvoice($invoice)
            ->withApiconnected($apiConnected)
            ->withContacts($invoiceContacts);
    }

    /**
     * Closed open payment
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function updatePayment(Request $request, $id)
    {
        $this->invoices->updatePayment($id, $request);
        return redirect()->back();
    }

    /**
     * Reopen closed payment
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function reopenPayment(Request $request, $id)
    {
        $this->invoices->reopenPayment($id, $request);
        return redirect()->back();
    }

    /**
     * Update the sent status
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function updateSentStatus(Request $request, $id)
    {
        $this->invoices->updateSentStatus($id, $request);
        return redirect()->back();
    }

    /**
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function updateSentReopen(Request $request, $id)
    {
        $this->invoices->updateSentReopen($id, $request);
        return redirect()->back();
    }

    /**
     * Add new invoice line
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function newItem($id, Request $request)
    {
        $this->invoices->newItem($id, $request);
        return redirect()->back();
    }
}
