<?php

namespace App\Observers;

use App\Models\Task;

class TaskObserver
{
    private $relations;

    public function __construct()
    {
        $this->relations = [
            'comments',
            'documents',
            'appointments',
            'activity',
        ];
    }

    /**
     * Handle the task "deleted" event.
     *
     * @param  \App\Models\Task  $task
     * @return void
     */
    public function deleted(Task $task)
    {
        foreach ($this->relations as $relation) {
            $task->$relation()->delete();
        }
    }

    /**
     * Handle the task "restored" event.
     *
     * @param  \App\Models\Task  $task
     * @return void
     */
    public function restored(Task $task)
    {
        foreach ($this->relations as $relation) {
            $task->$relation()->withTrashed()->restore();
        }
    }

    /**
     * Handle the task "force deleted" event.
     *
     * @param  \App\Models\Task  $task
     * @return void
     */
    public function forceDeleted(Task $task)
    {
        foreach ($this->relations as $relation) {
            $task->$relation()->forceDelete();
        }
    }
}
