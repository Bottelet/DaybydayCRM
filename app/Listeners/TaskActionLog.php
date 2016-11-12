<?php

namespace App\Listeners;

use App\Events\TaskAction;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Activity;
use Lang;

class TaskActionLog
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
     * @param  TaskAction  $event
     * @return void
     */
    public function handle(TaskAction $event)
    {
        switch ($event->getAction()) {
            case 'created':
              $text = Lang::get('misc.log.task.created', [
                        'title' => $event->getTask()->title,
                        'creator' => $event->getTask()->taskCreator->name,
                        'assignee' => $event->getTask()->assignee->name
                    ]);
                break;
            case 'updated_status':
              $text = Lang::get('misc.log.task.status', [
                        'username' => Auth()->user()->name,
                    ]);
                break;
            case 'updated_time':
                $text = Lang::get('misc.log.task.time', [
                        'username' => Auth()->user()->name,
                    ]);;
                break;
            case 'updated_assign':
               $text = Lang::get('misc.log.task.assign', [
                        'username' => Auth()->user()->name,
                        'assignee' => $event->getTask()->assignee->name
                    ]);
                break;
            default:
                break;
        }

        $activityinput = array_merge(
            [
                'text' => $text,
                'user_id' => Auth()->id(),
                'type' => 'task',
                'type_id' =>  $event->getTask()->id,
                'action' => $event->getAction()
            ]);
        
        Activity::create($activityinput);
    }
}
