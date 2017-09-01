<?php
namespace App\Repositories\Invoice;

use App\Models\Invoice;
use App\Models\Client;
use Carbon\Carbon;
use App\Models\Integration;
use App\Models\Task;
use Session;
use App\Models\InvoiceLine;
use App\Models\Activity;

class InvoiceRepository implements InvoiceRepositoryContract
{
    public function getAllInvoices()
    {
        return Invoice::all();
    }

    public function find($id)
    {
        return Invoice::findOrFail($id);
    }

     /**
     * @param $id
     * @param $requestData
     * @throws \Exception
     */
    public function invoice($id, $requestData)
    {
        $contatGuid = $requestData->invoiceContact;
      
        $invoice = Invoice::find($id);
        $invoice_lines = $invoice->invoiceLines;
        $sendMail = $requestData->sendMail;
        $productlines = [];

        foreach ($invoice_lines as $invoice_line) {
            $productlines[] = [
                'Description' => $invoice_line->title,
                'Comments' => $invoice_line->comment,
                'BaseAmountValue' => $invoice_line->price,
                'Quantity' => $invoice_line->quantity,
                'AccountNumber' => 1000,
                'Unit' => 'hours'];
        }

        $api = Integration::getApi('billing');

        $results = $api->createInvoice([
            'currency' => 'DKK',
            'description' => $invoice->title,
            'contact_id' => $contatGuid,
            'product_lines' => $productlines]);

        if ($sendMail == true) {
            $booked = $api->bookInvoice($results->Guid, $results->TimeStamp); 
            $bookGuid = $booked->Guid;
            $bookTime = $booked->TimeStamp;
            $api->sendInvoice($bookGuid, $bookTime);

            Activity::create([
                'text' => "user has created, and send the invoice to the customer",
                'user_id' => Auth()->id(),
                'source_type' => Invoice::class,
                'source_id' =>  $invoice->id,
                'action' => "sent_invoice"
            ]);
        }
    }

    public function updateSentStatus($id, $requestData)
    {
        $api = Integration::whereApiType('billing')->first();
        if ($api) {
            $this->invoice($id, $requestData);
        }

        $invoice = invoice::findOrFail($id);
        $input = array_replace($requestData->all(), ['sent_at' => Carbon::now(), 'status' => 'sent']);
        $invoice->fill($input)->save();
    }

    public function updatePayment($id, $requestData)
    {
        $invoice = invoice::findOrFail($id);
        $input = $requestData->get('payment_date');
        $input = array_replace($requestData->all(), ['due_at' => $input, 'payment_received_at' => Carbon::now()]);
        $invoice->fill($input)->save();
    }

    public function reopenPayment($id, $requestData)
    {
        $invoice = invoice::findOrFail($id);
        $input = array_replace($requestData->all(), ['payment_received_at' => null]);
        $invoice->fill($input)->save();
    }

    public function newItem($id, $request)
    {
        $invoice = invoice::findOrFail($id);

        if (!$invoice->canUpdateInvoice()) {
            return Session::flash('flash_message_warning', __("Can't insert new invoice line, to already sent invoice"));
        }
      
        InvoiceLine::create([
                'title' => $request->title,
                'comment' => $request->comment,
                'quantity' => $request->quantity,
                'type' => $request->type,
                'price' => $request->price,
                'invoice_id' => $invoice->id
            ]);
    }

    public function destroy($id)
    {
        return Invoice::destroy($id);
    }

    public function getAllOpenInvoices()
    {
    }

    public function getAllClosedInvoices()
    {
    }

    public function GetAllSentInvoices()
    {
    }

    public function GetAllNotSentInvoices()
    {
    }

    public function GetAllInvoicesPaymentNotReceived()
    {
    }
}
