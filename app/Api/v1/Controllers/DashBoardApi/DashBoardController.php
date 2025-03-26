<?php

namespace App\Api\v1\Controllers\DashBoardApi;



use App\Enums\InvoiceStatus;
use App\Enums\OfferStatus;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Dashboard\DashboardStats;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Lead;
use App\Models\Offer;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Support\Facades\DB;

class DashBoardController extends Controller
{
    public function getStats()
    {
        $nb_clients = Client::count();
        $nb_projects = Project::count();
        $nb_leads = Lead::count();
        $nb_tasks = Task::count();
        $nb_offers = Offer::count();
        $nb_offers_conclude = Offer::where('status', 'like', OfferStatus::won()->getStatus())->count();
        $nb_offers_decline = Offer::where('status', 'like', OfferStatus::lost()->getStatus())->count();
        $nb_offers_progress = Offer::where('status', 'like', OfferStatus::inProgress()->getStatus())->count();
        $nb_invoices = Invoice::count();
        $nb_payment = Payment::count();

        $dashboardData = new DashboardStats(
            $nb_clients,
            $nb_projects,
            $nb_tasks,
            $nb_offers,
            $nb_offers_conclude,
            $nb_offers_decline,
            $nb_offers_progress,
            $nb_invoices,
            $nb_payment,
            $nb_leads
        );

        return response()->json([
            'dashboardData' => $dashboardData,
        ], 200);

    }

    public function getBestClients()
    {
        $liste_clients = Client::all();
        $client_achat = collect([]);

        for ($i = 0; $i < count($liste_clients); $i++) {
            $amount_paid = 0;
            $liste_invoice = $liste_clients[$i]->invoices()->where('status', 'like', InvoiceStatus::paid()->getStatus())
                ->orWhere('status', 'like', InvoiceStatus::partialPaid()->getStatus())->where('client_id', $liste_clients[$i]->id)->get();
            for ($j = 0; $j < count($liste_invoice); $j++) {
                $amount_paid += $liste_invoice[$j]->getAlreadyPaid()->getBigDecimalAmount();
            }
            $client_achat->put($liste_clients[$i]->company_name, $amount_paid);
        }
        $top3 = $client_achat->sortDesc()->take(3);
        return response()->json([
            "top_3_clients" => $top3
        ], 200);
    }

    public function getOfferWon()
    {
        // Récupérer les offres gagnées
        $liste_offres = Offer::where('status', 'like', OfferStatus::won()->getStatus())->get();

        $gains = 0;

        foreach ($liste_offres as $offre) {
            // Récupérer les factures associées à cette offre
            $invoices = $offre->invoice()->where('offer_id', $offre->id)->get();

            if ($invoices->isNotEmpty()) {
                foreach ($invoices as $invoice) {
                    $gains += $invoice->getAlreadyPaid()->getBigDecimalAmount();
                }
            }
        }

        return response()->json([
            "offre_gains" => $gains
        ], 200);
    }


    public function getVentesMensuelles()
    {
        // Récupérer les factures avec statut payé ou partiellement payé en 2025
        $invoices = Invoice::whereYear('due_at', 2025)
            ->where(function ($query) {
                $query->where('status', InvoiceStatus::partialPaid()->getStatus())
                    ->orWhere('status', InvoiceStatus::paid()->getStatus());
            })
            ->get();

        // Initialiser un tableau avec tous les mois à 0
        $ventesMensuelles = array_fill(1, 12, 0);

        // Ajouter les montants des factures aux mois correspondants
        foreach ($invoices as $invoice) {
            $mois = (int) $invoice->due_at->format('n'); // Mois sous forme d'entier (1-12)
            $ventesMensuelles[$mois] += $invoice->getAlreadyPaid()->getBigDecimalAmount();
        }

        // Reformater les données pour une meilleure lisibilité
        $result = [];
        foreach ($ventesMensuelles as $mois => $montant) {
            $result[] = [

                'mois' => $mois,
                'montant' => $montant
            ]
            ;
        }

        // Retourner les données en JSON
        return response()->json(['ventes' => $result]);
    }
    public function getBestProducts()
    {
        $list_products = Product::all();
        $averina = collect([]);




        foreach ($list_products as $product) {
            $total_amount = InvoiceLine::where('product_id', $product->id)
                ->whereHas('invoice', function ($query) {
                    $query->whereIn('status', [InvoiceStatus::partialPaid()->getStatus(), InvoiceStatus::paid()->getStatus(), InvoiceStatus::unpaid()->getStatus()]);
                })
                ->sum(DB::raw('price * quantity'));

            $averina->put($product->name, $total_amount / 100);
        }
        $top3 = $averina->sortDesc()->take(3);
        return response()->json(['top_produits' => $top3], 200);
    }

    public function getInvoicesTotal()
    {
        $paidAmount = 0;
        $remainingAmount = 0;
        $invoices = Invoice::whereNotNull('offer_id')->get();
        foreach ($invoices as $invoice) {
            $paidAmount += $invoice->getAlreadyPaid()->getBigDecimalAmount();
            $remainingAmount += $invoice->getTotalPriceAttribute()->getBigDecimalAmount()- $invoice->getAlreadyPaid()->getBigDecimalAmount();
        }
        return response()->json([
            'invoice_total'=>[
            'Paye' => $paidAmount,
            'Non paye' => $remainingAmount
            ]
        ]);
    }

    





}


?>