<?php

namespace App\Listeners;

use App\Events\TaskAction;
use App\Notifications\TaskActionNotification;

class TaskActionNotify
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param TaskAction $event
     */
    public function handle(TaskAction $event)
    {
        $task   = $event->getTask();
        $action = $event->getAction();
        $task->assignedUser->notify(new TaskActionNotification(
            $task,
            $action
        ));
    }
}
