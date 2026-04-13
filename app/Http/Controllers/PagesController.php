<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\Client;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Setting;
use App\Models\Task;
use App\Models\User;
use Carbon\CarbonPeriod;

class PagesController extends Controller
{
    /**
     * Dashobard view.
     *
     * @return mixed
     */
    public function dashboard()
    {
        $today     = today();
        $startDate = today()->subdays(14);
        $period    = CarbonPeriod::create($startDate, $today);
        $datasheet = [];

        // Iterate over the period
        foreach ($period as $date) {
            $datasheet[$date->format(carbonDate())]                     = [];
            $datasheet[$date->format(carbonDate())]['monthly']          = [];
            $datasheet[$date->format(carbonDate())]['monthly']['tasks'] = 0;
            $datasheet[$date->format(carbonDate())]['monthly']['leads'] = 0;
        }

        $tasks = Task::whereBetween('created_at', [$startDate, now()])->get();
        $leads = Lead::whereBetween('created_at', [$startDate, now()])->get();
        foreach ($tasks as $task) {
            $datasheet[$task->created_at->format(carbonDate())]['monthly']['tasks']++;
        }

        foreach ($leads as $lead) {
            $datasheet[$lead->created_at->format(carbonDate())]['monthly']['leads']++;
        }
        if ( ! auth()->user()->can('absence-view')) {
            $absences = [];
        } else {
            // Get the latest qualifying absence per user at the database level
            $absences = Absence::with('user')
                ->where(function ($query) {
                    $query->where('start_at', '>=', today())
                        ->orWhere('end_at', '>', today());
                })
                ->whereRaw(
                    'start_at = (
                        select max(a2.start_at)
                        from absences as a2
                        where a2.user_id = absences.user_id
                          and (a2.start_at >= ? or a2.end_at > ?)
                    )',
                    [today(), today()]
                )
                ->orderBy('user_id')
                ->orderByDesc('start_at')
                ->get();
        }

        return view('pages.dashboard')
            ->withUsers(User::with(['department'])->get())
            ->withDatasheet($datasheet)
            ->withTotalTasks(Task::count())
            ->withTotalLeads(Lead::count())
            ->withTotalProjects(Project::count())
            ->withTotalClients(Client::count())
            ->withSettings(Setting::first())
            ->withAbsencesToday($absences);
    }
}
