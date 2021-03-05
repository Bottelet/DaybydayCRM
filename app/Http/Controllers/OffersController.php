<?php
namespace App\Http\Controllers;

use App\Models\Offer;

class OffersController extends Controller
{

    public function getOfferInvoiceLinesJson(Offer $offer)
    {
        return $offer->invoiceLines()->get(['title', 'comment', 'price', 'quantity', 'type']);
    }

}