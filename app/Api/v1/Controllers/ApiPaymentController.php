<?php
namespace App\Api\v1\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Models\Payment;
use App\Models\InvoiceReduction;
use Illuminate\Routing\Controller;

class ApiPaymentController extends Controller
{
    public function deletePayment($id): JsonResponse
    {
        try {
            $payment = Payment::findOrFail($id);
            
            DB::transaction(function() use ($payment) {
                $payment->deleted_at = now();
                $payment->save();
            });

            return response()->json([
                'success' => true,
                'message' => 'Paiement marqué comme supprimé',
                'deleted_at' => $payment->deleted_at
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Paiement non trouvé'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updatePayment($id, $amount): JsonResponse
    {
        try {
            \DB::beginTransaction();
            $payment = Payment::findOrFail($id);

            try {
                $payment->updated_at = now();
                $payment->amount = $amount*100;
                $payment->save();

                $sommeFacture = 0;
                foreach ($payment->invoice->invoiceLines as $line) {
                    $sommeFacture += $line->price*100;
                }

                /*$reduction = InvoiceReduction::whereId(1)->first();

                $sommeFacture = $sommeFacture - ($sommeFacture*($reduction->reduction/100));*/

                if($sommeFacture < ($payment->invoice->payments->sum('amount'))) {
                    \DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Le montant du paiement est superieur au montant de la facture',
                        'sommeFacture' => $sommeFacture,
                        'sommePaiements' => $payment->invoice->payments->sum('amount')
                    ], 200);
                }

                info('Amount: ' . $payment->amount);
                \DB::commit();
            } catch (\Exception $e) {
                \DB::rollBack();
                throw $e;
            }
            
            /*DB::transaction(function() use ($payment, $amount) {
                $payment->updated_at = now();
                $payment->amount = $amount;
                $payment->save();
                info('Amount: ' . $payment->amount);
            });*/

            return response()->json([
                'success' => true,
                'message' => 'Paiement mis a jour',
                'updated_at' => $payment->updated_at
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Paiement non trouvé'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la MIS A JOUR',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}