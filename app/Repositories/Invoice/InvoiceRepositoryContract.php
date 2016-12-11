<?php
namespace App\Repositories\Invoice;

interface InvoiceRepositoryContract
{
    public function getAllInvoices();

    public function getAllOpenInvoices();

    public function getAllClosedInvoices();

    public function GetAllSentInvoices();

    public function GetAllNotSentInvoices();
 
    public function GetAllInvoicesPaymentNotReceived();

    public function updatePayment($id, $requestData);

    public function reopenPayment($id, $requestData);

    public function updateSentStatus($id, $requestData);

    public function newItem($id, $requestData);

    public function updateSentReopen($id, $requestData);

    public function find($id);

    public function create($clientid, $timetaskid, $requestData);

    public function destroy($id);
}
