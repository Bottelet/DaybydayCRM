<?php

namespace App\Observers;

use App\Models\Project;

class ProjectObserver
{
    private $relations;

    public function __construct()
    {
        $this->relations = [
            'comments',
            'activity',
            'documents',
        ];
    }

    /**
     * Handle the project "created" event.
     *
     * @param  \App\Models\Project  $project
     * @return void
     */
    public function created(Project $project)
    {
        //
    }

    /**
     * Handle the project "updated" event.
     *
     * @param  \App\Models\Project  $project
     * @return void
     */
    public function updated(Project $project)
    {
        //
    }

    /**
     * Handle the project "deleted" event.
     *
     * @param  \App\Models\Project  $project
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
     * @param  \App\Models\Project  $project
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
     * @param  \App\Models\Project  $project
     * @return void
     */
    public function forceDeleted(Project $project)
    {
        foreach ($this->relations as $relation) {
            $project->$relation()->forceDelete();
        }
    }
}
