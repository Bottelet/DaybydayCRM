<?php

namespace App\Listeners;

use App\Events\TaskAction;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Activity;

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
                $text = $event->getTask()->title .
                ' was created by '. $event->getTask()->taskCreator->name .
                ' and assigned to ' . $event->getTask()->assignee->name;
                break;
            case 'updated_status':
                $text = 'Task was completed by '. Auth()->user()->name;
                break;
            case 'updated_time':
                $text = Auth()->user()->name.' Inserted a new time for this task';
                break;
            case 'updated_assign':
                $text = auth()->user()->name.' assigned task to '. $event->getTask()->assignee->name;
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
