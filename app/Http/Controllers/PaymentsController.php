<?php

namespace App\Http\Controllers;

use App\Http\Requests\Payment\PaymentRequest;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Response;
use InvalidArgumentException;

class PaymentsController extends Controller
{
    public function __construct(private PaymentService $paymentService) {}

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {}

    /**
     * Remove the specified resource from storage.
     *
     * @return mixed
     */
    public function destroy(Payment $payment, \Illuminate\Http\Request $request)
    {
        if ( ! auth()->user()->can('payment-delete')) {
            if ($request->expectsJson()) {
                return response()->json(['message' => __("You don't have permission to delete a payment")], 403);
            }
            session()->flash('flash_message', __("You don't have permission to delete a payment"));

            return redirect()->back();
        }

        $this->paymentService->deletePayment($payment);

        if ($request->expectsJson()) {
            return response()->json(['message' => __('Payment successfully deleted')], 200);
        }

        session()->flash('flash_message', __('Payment successfully deleted'));

        return redirect()->back();
    }

    public function addPayment(PaymentRequest $request, Invoice $invoice)
    {
        if ( ! auth()->user()->can('payment-create')) {
            if ($request->expectsJson()) {
                return response()->json(['message' => __("You don't have permission to add a payment")], 403);
            }
            session()->flash('flash_message', __("You don't have permission to add a payment"));

            return redirect()->back();
        }

        if ( ! $invoice->isSent()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => __("Can't add payment on Invoice")], 422);
            }
            session()->flash('flash_message_warning', __("Can't add payment on Invoice"));

            return redirect()->route('invoices.show', $invoice->external_id);
        }

        try {
            $this->paymentService->addPayment(
                $invoice,
                (float) $request->amount,
                $request->payment_date,
                $request->source,
                $request->description ?? null,
            );
        } catch (InvalidArgumentException $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
            session()->flash('flash_message_warning', $e->getMessage());

            return redirect()->route('invoices.show', $invoice->external_id);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => __('Payment successfully added')], 201);
        }

        session()->flash('flash_message', __('Payment successfully added'));

        return redirect()->back();
    }
}
