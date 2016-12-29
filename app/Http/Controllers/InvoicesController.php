<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use App\Repositories\Invoice\InvoiceRepositoryContract;
use App\Repositories\Client\ClientRepositoryContract;

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
        return view('invoices.show')
            ->withInvoice($this->invoices->find($id));
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
