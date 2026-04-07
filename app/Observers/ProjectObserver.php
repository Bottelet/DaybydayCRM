<?php

namespace App\Observers;

use App\Models\Project;

class ProjectObserver
{
    private $relations;

    public function __construct()
    {
        if (app()->environment('testing')) {
            return;
        }
        $this->relations = [
            'comments',
            'activity',
            'documents',
        ];
    }

    /**
     * Handle the project "created" event.
     *
     * @return void
     */
    public function created(Project $project)
    {
        //
    }

    /**
     * Handle the project "updated" event.
     *
     * @return void
     */
    public function updated(Project $project)
    {
        //
    }

    /**
     * Handle the project "deleted" event.
     *
     * @return void
     */
    public function deleted(Project $project)
    {
        foreach ($this->relations as $relation) {
            $project->$relation()->delete();
        }
    }

    /**
     * Handle the project "restored" event.
     *
     * @return void
     */
    public function restored(Project $project)
    {
        foreach ($this->relations as $relation) {
            $project->$relation()->withTrashed()->restore();
        }
    }

    /**
     * Handle the project "force deleted" event.
     *
     * @return void
     */
    public function forceDeleted(Project $project)
    {
        foreach ($this->relations as $relation) {
            $project->$relation()->forceDelete();
        }
    }
}
