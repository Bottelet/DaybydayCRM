<?php

namespace App\Listeners;

use App\Events\ProjectAction;
use App\Notifications\ProjectActionNotification;

class ProjectActionNotify
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(ProjectAction $event)
    {
        $Project = $event->getProject();
        $action = $event->getAction();
        $Project->assignee->notify(new ProjectActionNotification(
            $Project,
            $action
        ));
    }
}
