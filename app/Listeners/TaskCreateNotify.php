<?php

namespace App\Listeners;

use App\Events\TaskCreate;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\TaskCreateNotification;

class TaskCreateNotify
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
        $task->assignedUser->notify(new TaskCreateNotification($task));
    }
}
