<?php

namespace App\Http\Controllers;

use App\Http\Requests\Offer\CreateOfferRequest;
use App\Models\Client;
use App\Models\Lead;
use App\Models\Offer;
use App\Services\Offer\OfferService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Throwable;

class OffersController extends Controller
{
    public function __construct(private OfferService $offerService)
    {
        $this->middleware('permission:offer-create', ['only' => ['create']]);
        $this->middleware('permission:offer-edit', ['only' => ['update', 'won', 'lost']]);
    }

    public function getOfferInvoiceLinesJson(Offer $offer)
    {
        return $offer->invoiceLines()->with(['product' => function ($q) {
            $q->select('id', 'external_id', 'name');
        }])->get(['title', 'comment', 'price', 'quantity', 'type', 'product_id']);
    }

    public function update(Request $request, Offer $offer)
    {
        try {
            $this->offerService->replaceInvoiceLines($offer, $request->all());
        } catch (InvalidArgumentException $exception) {
            return response($exception->getMessage(), 422);
        }
    }

    public function create(CreateOfferRequest $request, string $external_id)
    {
        try {
            $lead = Lead::query()->where('external_id', $external_id)->first();
            if ($lead) {
                if ( ! $lead->client_id) {
                    return $this->createOfferErrorResponse(
                        $request,
                        __('This lead must be associated with a client before creating an offer'),
                        422
                    );
                }

                return $this->createOfferForSource($request, $lead->id, $lead->client_id, Lead::class);
            }

            $client = Client::query()->where('external_id', $external_id)->first();
            if ( ! $client) {
                return $this->createOfferErrorResponse($request, __('Offer source was not found'), 404);
            }

            return $this->createOfferForSource($request, $client->id, $client->id, Client::class);
        } catch (ValidationException $exception) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('The given data was invalid.'),
                    'errors'  => $exception->errors(),
                ], 422);
            }

            return redirect()->back()->withInput()->withErrors($exception->errors());
        } catch (Throwable $exception) {
            report($exception);

            return $this->failureResponse(
                $request,
                __('Offer could not be created. Please try again.'),
                'offer'
            );
        }
    }

    public function won(Request $request)
    {
        $offer = Offer::whereExternalId($request->get('offer_external_id'))->with('invoiceLines')->firstOrFail();
        $this->offerService->convertToInvoice($offer);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'OK'], 200);
        }

        return redirect()->back();
    }

    public function lost(Request $request)
    {
        $offer = Offer::whereExternalId($request->get('offer_external_id'))->firstOrFail();
        $this->offerService->markAsLost($offer);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'OK'], 200);
        }

        return redirect()->back();
    }

    private function createOfferForSource(CreateOfferRequest $request, int $sourceId, int $clientId, string $sourceType)
    {
        $this->offerService->createForSource($request->validated(), $sourceId, $clientId, $sourceType);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'OK'], 200);
        }

        return response('OK');
    }

    private function createOfferErrorResponse(CreateOfferRequest $request, string $message, int $statusCode)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], $statusCode);
        }

        session()->flash('flash_message_warning', $message);

        return redirect()->back();
    }
}
