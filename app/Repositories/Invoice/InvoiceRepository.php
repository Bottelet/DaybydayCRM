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
use Ramsey\Uuid\Uuid;

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

    public function findByExternalId($external_id)
    {
        return Invoice::whereExternalId($external_id)->first();
    }


    /**
    * @param $external_id
    * @param $requestData
    * @throws \Exception
    */
    public function invoice($external_id, $requestData)
    {
        $contacts = explode(',', $requestData->invoiceContact);
        $contactGuid = $contacts[0];
        $contact_person_id = $contacts[1];

        $invoice = $this->findByExternalId($external_id);
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
                'Unit' => $invoice_line->type];
        }

        $api = Integration::getApi('billing');

        $results = $api->createInvoice([
            'currency' => 'DKK',
            'description' => $invoice->task->title,
            'contact_id' => $contactGuid,
            'contact_person_id' => $contact_person_id,
            'product_lines' => $productlines]);
        
        if ($sendMail == true) {
            $booked = $api->bookInvoice($results->Guid, $results->TimeStamp);
            $bookGuid = $booked->Guid;
            $bookTime = $booked->TimeStamp;
            
            $api->sendInvoice($bookGuid, $bookTime, $contactGuid);

            activity("task")
                ->performedOn($invoice)
                ->withProperties(['action' => "sent_invoice"])
                ->log("user has send the invoice to the customer");
        }
    }

    public function updateSentStatus($external_id, $requestData)
    {
        $api = Integration::whereApiType('billing')->first();
        if ($api && $requestData->invoiceContact) {
            $this->invoice($external_id, $requestData);
        }

        $invoice = $this->findByExternalId($external_id);
        $input = array_replace($requestData->all(), ['sent_at' => Carbon::now(), 'status' => 'sent']);
        $invoice->fill($input)->save();
    }

    public function updateSentReopen($external_id, $requestData)
    {
        $invoice = $this->findByExternalId($external_id);
        $input = array_replace($requestData->all(), ['sent' => null]);
        $invoice->fill($input)->save();
    }

    public function updatePayment($external_id, $requestData)
    {
        $invoice = $this->findByExternalId($external_id);
        $input = $requestData->get('payment_date');
        $input = array_replace($requestData->all(), ['due_at' => $input]);
        $invoice->fill($input)->save();
    }


    public function newItem($external_id, $request)
    {
        $invoice = $this->findByExternalId($external_id);

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

    public function destroy($external_id)
    {
        $invoice = $this->findByExternalId($external_id);
        return $invoice->delete();
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
