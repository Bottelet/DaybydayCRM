<?php

namespace App\Listeners;

use App\Events\TaskAction;
use App\Models\User;
use App\Services\Activity\ActivityLogger;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Activity;
use Lang;
use App\Models\Task;

class TaskActionLog
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
     * @param  TaskAction  $event
     * @return void
     */
    public function handle(TaskAction $event)
    {
        switch ($event->getAction()) {
            case 'created':
                $text = __(':title was created by :creator and assigned to :assignee', [
                        'title' => $event->getTask()->title,
                        'creator' => $event->getTask()->creator->name,
                        'assignee' => $event->getTask()->user->name
                    ]);
                break;
            case 'updated_status':
                $text = __('Task status was updated by :username', [
                        'username' => Auth()->user()->name,
                    ]);
                break;
            case 'updated_time':
                $text = __(':username inserted a new time for this task', [
                        'username' => Auth()->user()->name,
                    ]);
                ;
                break;
            case 'updated_assign':
                $text = __(':username assigned task to :assignee', [
                    'username' => Auth()->user()->name,
                    'assignee' => $event->getTask()->user->name
                ]);

                break;
            case 'updated_deadline':
                $text = __(':username updated the deadline for this task', [
                    'username' => Auth()->user()->name,
                ]);
                break;
            default:
                break;
        }

        activity("task")
            ->performedOn($event->getTask())
            ->withProperties(['action' => $event->getAction()])
            ->log($text);
    }
}
