<?php
namespace App\Repositories\Invoice;

use App\Models\Invoice;
use App\Models\Client;
use App\Models\TaskTime;

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

    public function create($clientid, $timetaskid, $requestData)
    {
        $invoice = Invoice::create();
        $invoice->clients()->attach($clientid);

        foreach ($timetaskid as $tk) {
            $testid[] = $tk->id;
        }

        $invoice->tasktime()->attach($testid);
        $invoice->save();
    }

    public function updateSentStatus($id, $requestData)
    {
        $invoice = invoice::findOrFail($id);
        $input = array_replace($requestData->all(), ['sent' => 1]);
        $invoice->fill($input)->save();
    }

    public function updateSentReopen($id, $requestData)
    {
        $invoice = invoice::findOrFail($id);
        $input = array_replace($requestData->all(), ['sent' => 0]);
        $invoice->fill($input)->save();
    }

    public function updatePayment($id, $requestData)
    {
        $invoice = invoice::findOrFail($id);
        $input = $requestData->get('payment_date');
        $input = array_replace($requestData->all(), ['payment_date' => $input, 'received' => 1]);
        $invoice->fill($input)->save();
    }

    public function reopenPayment($id, $requestData)
    {
        $invoice = invoice::findOrFail($id);
        $input = array_replace($requestData->all(), ['received' => 0]);
        $invoice->fill($input)->save();
    }

    public function newItem($id, $requestData)
    {
        $invoice = invoice::findOrFail($id);

        $tasktimeId = $invoice->tasktime()->first()->task_id;

        $clientid = $invoice->clients()->first()->id;

        $input = array_replace($requestData->all(), ['task_id' => "$tasktimeId"]);

        $tasktime = TaskTime::create($input);
        $insertedId = $tasktime->id;

        $invoice->tasktime()->attach($insertedId);
        $invoice->clients()->attach($clientid);
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
