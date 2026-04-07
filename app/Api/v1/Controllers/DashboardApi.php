<?php
namespace App\Api\v1\Controllers;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Offer;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\Creation\CreationTracker;
use App\Services\Earnings\EarningsService;
use App\Services\Product\ProductService;
use App\Services\Project\ProjectService;

class DashboardApi extends Controller
{
    public function index()
    {
        $clients=Client::count();
        $users=User::count();
        $projects=Project::count();
        $leads=Lead::count();
        $offers=Offer::count();
        $invoices=Invoice::count();
        $tasks=Task::count();
        $year=now()->year;
        $month=now()->month;

        $projectservice=new ProjectService();
        $openedprojects=$projectservice->getSumOpened();
        $canceledprojects=$projectservice->getSumCanceled();
        $inprogressprojects=$projectservice->getSumInprogress();
        $completedprojects=$projectservice->getSumCompleted();
        $blockedprojects=$projectservice->getSumBlocked();


        $creationservice=new CreationTracker();
        $creationdatasheet=$creationservice->tracker(15);


        $productservice=new ProductService();
        $bestproducts=$productservice->getTopProductsMonthly(3);


        $earningservice=new EarningsService();
        $daybydayearnings=$earningservice->getDaybyDayEarnings($year,$month);
        $monthearnings=$earningservice->getMonthlyEarnings($year,$month);
        $annualearnings=$earningservice->getAnnualEarnings($year);
        $globalearnings=$earningservice->getGlobalEarnings();



        return response()->json([
            'clients' => $clients,
            'users' => $users,
            'projects' => $projects,
            'leads' => $leads,
            'offers' => $offers,
            'tasks'=>$tasks,
            'invoices' => $invoices,
            'openedprojects' => $openedprojects,
            'canceledprojects' => $canceledprojects,
            'inprogressprojects' => $inprogressprojects,
            'completedprojects' => $completedprojects,
            'blockedprojects' => $blockedprojects,
            'creationdatasheet' => $creationdatasheet,
            'bestproducts' => $bestproducts,
            'daybydayearnings' => $daybydayearnings,
            'monthlyearnings' => $monthearnings,
            'annualearnings' => $annualearnings,
            'globalearnings' => $globalearnings,
        ]);

    }
}
?>