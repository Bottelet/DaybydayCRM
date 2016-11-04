<?php

namespace App\Listeners;

use App\Events\TaskCreate;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Activity;

class TaskCreateLog
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Handle the event.
     *
     * @param  TaskCreate  $event
     * @return void
     */
    public function handle(TaskCreate $event)
    {
        $task = $event->getTask();
        $activityinput = array_merge(
            [
                'text' => 'Task ' . $task->title .
                ' was created by '. $task->taskCreator->name .
                ' and assigned to ' . $task->assignee->name,
                'user_id' => Auth()->id(),
                'type' => 'task',
                'type_id' =>  $task->id
            ]);
        
        Activity::create($activityinput);
    }
}
