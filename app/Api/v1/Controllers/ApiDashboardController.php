<?php
namespace App\Api\v1\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use App\Models\{Client, Offer, Payment, Task, Project, Invoice};

class ApiDashboardController extends Controller
{
    public function dashboardData(): JsonResponse
    {
        $startDate = now()->subDays(14)->startOfDay();
        $endDate = now()->endOfDay();
        
        $dates = [];
        foreach (CarbonPeriod::create($startDate, $endDate) as $date) {
            $dates[] = $date->format('Y-m-d');
        }

        $graphTemplate = array_fill_keys($dates, 0);

        $graphs = [
            'tasks' => $this->getDailyCounts(Task::class, $startDate, $graphTemplate),
            'projects' => $this->getDailyCounts(Project::class, $startDate, $graphTemplate),
            'invoices' => $this->getDailyCounts(Invoice::class, $startDate, $graphTemplate),
        ];

        return response()->json([
            'totals' => [
                'clients' => Client::count(),
                'offers' => Offer::count(),
                'invoices' => Invoice::count(),
                'payments' => doubleval(Payment::sum('amount')/100),
                'factures' => $this->totalInvoice()
            ],
            'graphs' => $graphs
        ]);
    }

    private function getDailyCounts(string $modelClass, $startDate, array $template): array
    {
        $counts = $modelClass::whereBetween('created_at', [$startDate, now()])
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m-%d") as date, COUNT(*) as count')
            ->groupBy('date')
            ->get()
            ->pluck('count', 'date');

        return array_replace($template, $counts->toArray());
    }

    private function totalInvoice(){
        $total = DB::select("select sum(price*quantity) as total from invoice_lines where offer_id is null")[0]->total;

        $total = $total/100;
        return $total;
    }
}