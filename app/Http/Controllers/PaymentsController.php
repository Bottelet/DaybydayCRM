<?php

namespace App\Http\Controllers;

use App\Http\Requests\Payment\PaymentRequest;
use App\Models\Integration;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\Earnings\EarningsService;
use App\Services\Invoice\GenerateInvoiceStatus;
use App\Services\Invoice\InvoiceCalculator;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;
use App\Repositories\Money\Money;
class PaymentsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }


    public function getPayments(Request $request)
    {
        $month=$request->query('month', null);
        $year=$request->query('year', null);

        $myearning=new EarningsService();
        $payments=$myearning->loadPayments($year,$month);
        return response()->json($payments);
    }


    public function update(Request $request, $external_id)
    {
        try {
            $payment= Payment::getByExternalId($external_id);
            if (!$payment) {
                return response()->json([
                    'message' => 'Payment not found!',
                    'success' => false
                ]);
            }

            $payment->amount = $request->amount ;
            $payment->save();


            $invoice=$payment->invoice()->first();
            app(GenerateInvoiceStatus::class, ['invoice' => $invoice])->createStatus();

            return response()->json([
                'message' => 'Payment updated successfully!',
                'success' => true,
            ]);
        }
        catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'success' => false
            ]);
        }

    }



    public function destroyAPI( $external_id)
    {

        $payment= Payment::getByExternalId($external_id);
        if($payment){
            $invoice=$payment->invoice()->first();
            app(GenerateInvoiceStatus::class, ['invoice' => $invoice])->createStatus();
            $payment->delete();
            return response()->json([
                'success' => true,
                'message' => __('Payment successfully deleted')
            ]);
        }
        else{
            return response()->json([
                'success' => false,
                'message' => __('Payment not found')
            ]);
        }

    }






    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Payment $payment
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy(Payment $payment)
    {
        if (!auth()->user()->can('payment-delete')) {
            session()->flash('flash_message', __("You don't have permission to delete a payment"));
            return redirect()->back();
        }
        $api = Integration::initBillingIntegration();
        if ($api) {
            $api->deletePayment($payment);
        }
        #echo $payment->id;
        $invoice=$payment->invoice()->first();
        $payment->delete();
        session()->flash('flash_message', __('Payment successfully deleted'));
        app(GenerateInvoiceStatus::class, ['invoice' => $invoice])->createStatus();
        return redirect()->back();
    }

    public function addPayment(PaymentRequest $request, Invoice $invoice)
    {
        if (!$invoice->isSent()) {
            session()->flash('flash_message_warning', __("Can't add payment on Invoice"));
            return redirect()->route('invoices.show', $invoice->external_id);
        }


        try {
            $payment = Payment::create([
                'external_id' => Uuid::uuid4()->toString(),
                'invoice_id' => $invoice->id,
                'amount' => $request->amount * 100,
                'payment_date' => Carbon::parse($request->payment_date),
                'payment_source' => $request->source,
                'description' => $request->description

            ]);


            $api = Integration::initBillingIntegration();
            if ($api && $invoice->integration_invoice_id) {
                $result = $api->createPayment($payment);
                $payment->integration_payment_id = $result["Guid"];
                $payment->integration_type = get_class($api);
                $payment->save();
            }


            app(GenerateInvoiceStatus::class, ['invoice' => $invoice])->createStatus();

            session()->flash('flash_message', __('Payment successfully added'));

        }
        catch (\Exception $e)
        {
            #echo $e->getMessage();
            session()->flash('flash_message_warning', $e->getMessage());
        }
        return redirect()->back();
    }


}
