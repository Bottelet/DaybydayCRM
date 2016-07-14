<?php
namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Tasks;
use Carbon;
use App\Client;
use DB;
use App\User;
use App\Settings;
use App\Leads;

class PagesController extends Controller
{

    public function home()
    {
        return view('pages.home');
    }
    public function dashboard()
    {

        $settings = Settings::findOrFail(1);
        $companyname = $settings->company;

        $users = User::all();
        
        $totalClients = Client::all()->count();
        $completedTasksToday = Tasks::whereRaw(
            'date(updated_at) = ?',
            [Carbon::now()->format('Y-m-d')]
        )->where('status', 2)->count();
        $createdTasksToday = Tasks::whereRaw(
            'date(created_at) = ?',
            [Carbon::now()->format('Y-m-d')]
        )->count();

        $completedLeadsToday = Leads::whereRaw(
            'date(updated_at) = ?',
            [Carbon::now()->format('Y-m-d')]
        )->where('status', 2)->count();
        $createdLeadsToday = Leads::whereRaw(
            'date(created_at) = ?',
            [Carbon::now()->format('Y-m-d')]
        )->count();

        $createdTasksMonthly = DB::table('tasks')
                     ->select(DB::raw('count(*) as month, created_at'))
                     ->groupBy(DB::raw('YEAR(created_at), MONTH(created_at)'))
                     ->get();
        $completedTasksMonthly = DB::table('tasks')
                     ->select(DB::raw('count(*) as month, updated_at'))
                     ->where('status', 2)
                     ->groupBy(DB::raw('YEAR(updated_at), MONTH(updated_at)'))
                     ->get();
        $completedLeadsMonthly = DB::table('leads')
             ->select(DB::raw('count(*) as month, updated_at'))
             ->where('status', 2)
             ->groupBy(DB::raw('YEAR(updated_at), MONTH(updated_at)'))
             ->get();

        $createdLeadsMonthly = DB::table('leads')
         ->select(DB::raw('count(*) as month, created_at'))
         ->groupBy(DB::raw('YEAR(created_at), MONTH(created_at)'))
         ->get();

        $taskCompletedThisMonth = DB::table('tasks')
                     ->select(DB::raw('count(*) as total, updated_at'))
                     ->where('status', 2)
                     ->whereBetween('updated_at', array(Carbon::now()->startOfMonth(), Carbon::now()))->get();
        $leadCompletedThisMonth = DB::table('leads')
                     ->select(DB::raw('count(*) as total, updated_at'))
                     ->where('status', 2)
                     ->whereBetween('updated_at', array(Carbon::now()->startOfMonth(), Carbon::now()))->get();
      
        $totalTimeSpent = DB::table('tasks_time')
         ->select(DB::raw('SUM(time)'))
         ->get();

        $alltasks = Tasks::all()->count();
        $allCompletedTasks = Tasks::where('status', 2)->count();
        if (!$alltasks || !$allCompletedTasks) {
            $totalPercentageTasks = 0;
        } else {
            $totalPercentageTasks =  $allCompletedTasks / $alltasks * 100;
        }

        $allleads = Leads::all()->count();
        $allCompletedLeads = Leads::where('status', 2)->count();
        if (!$allleads || !$allCompletedLeads) {
            $totalPercentageLeads = 0;
        } else {
            $totalPercentageLeads =   $allCompletedLeads / $allleads * 100;
        }

       
        return view('pages.dashboard', compact(
            'completedTasksToday',
            'completedLeadsToday',
            'createdTasksToday',
            'createdLeadsToday',
            'createdTasksMonthly',
            'completedTasksMonthly',
            'completedLeadsMonthly',
            'createdLeadsMonthly',
            'taskCompletedThisMonth',
            'leadCompletedThisMonth',
            'totalTimeSpent',
            'totalClients',
            'users',
            'companyname',
            'alltasks',
            'allCompletedTasks',
            'totalPercentageTasks',
            'allleads',
            'allCompletedLeads',
            'totalPercentageLeads'
        ));
    }
}
