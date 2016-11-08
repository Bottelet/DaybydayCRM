<?php

namespace App\Listeners;

use App\Events\TaskAction;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\TaskActionNotification;

class TaskActionNotify
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
    public function handle(TaskAction $event)
    {
        $task = $event->getTask();
        $action = $event->getAction();
        $text = $event->getText();
        $task->assignedUser->notify(new TaskActionNotification(
            $task,
            $action,
            $text
            ));
    }
}
