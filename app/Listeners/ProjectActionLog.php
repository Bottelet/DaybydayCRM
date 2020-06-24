<?php

namespace App\Listeners;

use App\Events\ProjectAction;
use App\Models\User;
use App\Services\Activity\ActivityLogger;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Activity;
use Lang;
use App\Models\Project;

class ProjectActionLog
{
    /**
     * Create the event listener.
     *
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param  ProjectAction  $event
     * @return void
     */
    public function handle(ProjectAction $event)
    {
        switch ($event->getAction()) {
            case 'created':
                $text = __(':title was created by :creator and assigned to :assignee', [
                        'title' => $event->getProject()->title,
                        'creator' => $event->getProject()->creator->name,
                        'assignee' => $event->getProject()->assignee->name
                    ]);
                break;
            case 'updated_status':
                $text = __('Project status was updated by :username', [
                        'username' => Auth()->user()->name,
                    ]);
                break;
            case 'updated_time':
                $text = __(':username inserted a new time for this project', [
                        'username' => Auth()->user()->name,
                    ]);
                ;
                break;
            case 'updated_assign':
                $text = __(':username assigned Project to :assignee', [
                    'username' => Auth()->user()->name,
                    'assignee' => $event->getProject()->assignee->name
                ]);

                break;
            case 'updated_deadline':
                $text = __(':username updated the deadline for this project', [
                    'username' => Auth()->user()->name,
                ]);
                break;
            default:
                break;
        }

        activity("project")
            ->performedOn($event->getProject())
            ->withProperties(['action' => $event->getAction()])
            ->log($text);
    }
}
