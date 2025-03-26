<?php
namespace App\Api\v1\Controllers\DashBoardApi;

use App\Enums\InvoiceStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Payment;
class DetailsTotalController extends Controller
{


    public function getInvoices()
    {
        $invoices = Invoice::whereNotNull("offer_id")->get()->map(function ($invoice) {
            return [

                'id' => $invoice->id,
                'total_amount' => $invoice->getTotalPriceAttribute()->getBigDecimalAmount(),
                'paid_amount' => $invoice->getAlreadyPaid()->getBigDecimalAmount(),
                'remaining_amount' => $invoice->getTotalPriceAttribute()->getBigDecimalAmount() -
                    $invoice->getAlreadyPaid()->getBigDecimalAmount(),
                'status' => $invoice->status
            ]
            ;
        });

        return response()->json(['invoices' => $invoices]);
    }

    public function getInvoiceById(Request $request)
    {
        // Récupérer la facture
        $invoice = Invoice::where("id", $request->input('id'))->first();
    
        // Vérifier si la facture existe
        if (!$invoice) {
            return response()->json(['error' => 'Facture non trouvée'], 404);
        }
    
        // Construire la réponse
        $invoiceData = [
            'id' => $invoice->id,
            'total_amount' => $invoice->getTotalPriceAttribute()->getBigDecimalAmount(),
            'paid_amount' => $invoice->getAlreadyPaid()->getBigDecimalAmount(),
            'remaining_amount' => $invoice->getTotalPriceAttribute()->getBigDecimalAmount() - 
                $invoice->getAlreadyPaid()->getBigDecimalAmount(),
            'status' => $invoice->status
        ];
    
        return response()->json(['invoice' => $invoiceData]);
    }
    

    public function getPayments(Request $request){
        
        $payment = Payment::where("invoice_id",$request->input('id'))->get()->map(
            function($payment){
                return [
                    'id'=>$payment->id,
                    'amount'=>$payment->amount/100,
                    'description'=>$payment->description,
                    'payment_source'=>$payment->payment_source,
                    'payment_date'=>$payment->payment_date,
                    'invoice_id'=>$payment->invoice_id
                ];
            }
        );
        return response()->json(['payments'=>$payment]);
    }

    public function getPaymentById(Request $request){
        $payment = Payment::where("id",$request->input('id'))->first()
            ;
            $payment->amount = $payment->amount/100;
        return response()->json(['payment'=>$payment]);
    }

    public function editPayment(Request $request){
        $payment = Payment::where("id",$request->input("id"))->first();
        $invoice = Invoice::where("id",$payment->invoice_id)->first();
        $payment->amount = $request->input("amount")*100;
        $payment->save();
        $reste = $invoice->getTotalPriceAttribute()->getBigDecimalAmount()- $invoice->getAlreadyPaid()->getBigDecimalAmount() ;
        if($reste==0){
            $invoice->status=InvoiceStatus::paid()->getStatus();
            $invoice->save();
        }
        else if($invoice->getAlreadyPaid()->getBigDecimalAmount() > 0){
            $invoice->status=InvoiceStatus::partialPaid()->getStatus();
            $invoice->save();
        }
        else{
            $invoice->status=InvoiceStatus::unpaid()->getStatus();
            $invoice->save();
        }

        return response()->json(['payment'=>$payment]); 
    }

    public function deletePayment(Request $request){
        $payment = Payment::where("id",$request->input("id"))->first();
        $invoice = Invoice::where("id",$payment->invoice_id)->first();
        $payment->delete();
        

        $reste = $invoice->getTotalPriceAttribute()->getBigDecimalAmount()- $invoice->getAlreadyPaid()->getBigDecimalAmount() ;
        if($reste==0){
            $invoice->status=InvoiceStatus::paid()->getStatus();
            $invoice->save();
        }
        else if($invoice->getAlreadyPaid()->getBigDecimalAmount() > 0){
            $invoice->status=InvoiceStatus::partialPaid()->getStatus();
            $invoice->save();
        }
        else{
            $invoice->status=InvoiceStatus::unpaid()->getStatus();
            $invoice->save();
        }

        return response()->json(['message'=>"Payment supprime"]); 
    }


}
?>