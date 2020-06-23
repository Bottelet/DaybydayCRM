<?php

namespace App\Listeners;

use App\Events\ProjectAction;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\ProjectActionNotification;

class ProjectActionNotify
{
    /**
     * Create the event listener.
     *
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  ProjectAction  $event
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
