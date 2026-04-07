<?php

namespace App\Services\Creation;

use App\Models\Lead;
use App\Models\Offer;
use App\Models\Project;
use App\Models\Task;
use Carbon\CarbonPeriod;

class CreationTracker
{
    public function tracker($days)
    {
        $today = today();
        $startDate = today()->subdays($days);
        $period = CarbonPeriod::create($startDate, $today);
        $datasheet = [];

        // Iterate over the period
        foreach ($period as $date) {
            $datasheet[$date->format(carbonDate())] = [];
            $datasheet[$date->format(carbonDate())] = [];
            $datasheet[$date->format(carbonDate())]["tasks"] = 0;
            $datasheet[$date->format(carbonDate())]["projects"] = 0;
            $datasheet[$date->format(carbonDate())]["leads"] = 0;
            $datasheet[$date->format(carbonDate())]["offers"] = 0;
        }

        $tasks = Task::whereBetween('created_at', [$startDate, now()])->get();
        $leads = Lead::whereBetween('created_at', [$startDate, now()])->get();
        $projects=Project::whereBetween('created_at', [$startDate, now()])->get();
        $offers=Offer::whereBetween('created_at', [$startDate, now()])->get();

        foreach ($tasks as $task) {
            $datasheet[$task->created_at->format(carbonDate())]["tasks"]++;
        }

        foreach ($leads as $lead) {
            $datasheet[$lead->created_at->format(carbonDate())]["leads"]++;
        }
        foreach ($projects as $project) {
            $datasheet[$project->created_at->format(carbonDate())]["projects"]++;
        }

        foreach ($offers as $offer) {
            $datasheet[$offer->created_at->format(carbonDate())]["offers"]++;
        }
        return $datasheet;
    }
}