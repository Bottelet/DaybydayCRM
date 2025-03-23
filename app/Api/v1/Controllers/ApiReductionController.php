<?php
namespace App\Api\v1\Controllers;

use Illuminate\Http\JsonResponse;
use App\Models\InvoiceReduction;
use Illuminate\Routing\Controller;

class ApiReductionController extends Controller
{
    public function reductionValue(): JsonResponse
    {
        $reduction = InvoiceReduction::whereId(1)->first();
        return response()->json([
            'success' => true,
            'reduction' => $reduction->reduction
        ]);
    }

    public function updateReduction($amount): JsonResponse
    {
        $reduction = InvoiceReduction::whereId(1)->first();
        $reduction->reduction = $amount;
        $reduction->updated_at = now();
        $reduction->save();
        return response()->json([
            'success' => true,
            'message' => 'Reduction updated',
            'reduction' => $reduction->reduction
        ]);
    }
}