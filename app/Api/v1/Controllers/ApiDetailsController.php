<?php
namespace App\Api\v1\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use App\Models\Client;
use App\Models\Offer;
use App\Models\Payment;
use Illuminate\Routing\Controller;

class ApiDetailsController extends Controller
{
    public function clientsDetails(): JsonResponse
    {
        try {
            $clients = Client::select(['id', 'company_name', 'address', 'city'])
                            ->whereNull('deleted_at')
                            ->orderBy('created_at', 'desc')
                            ->get();

            return response()->json([
                'success' => true,
                'clients' => $clients
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des clients'
            ], 500);
        }
    }

    public function offersDetails(): JsonResponse
    {
        try {
            $offers = Offer::select(['id', 'client_id', 'status'])
                            ->whereNull('deleted_at')
                            ->orderBy('created_at', 'desc')
                            ->get();

            return response()->json([
                'success' => true,
                'offers' => $offers
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Offers Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur technique'
            ], 500);
        }
    }

    public function paymentsDetails(): JsonResponse
    {
        try {
            $payments = Payment::select(['id', 'amount', 'description', 'payment_source', 'payment_date'])
                ->whereNull('deleted_at')
                ->orderBy('payment_date', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'payments' => $payments
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des paiements'
            ], 500);
        }
    }

    public function invoiceLinesDetails()
    {
        try{

            $invoiceLines = DB::select("select title, price, quantity from invoice_lines where offer_id is null and deleted_at is null");

            return response()->json([
                'success' => true,
                'invoiceLines' => $invoiceLines
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des paiements'
            ], 500);
        }

    }
}