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
     * @param  TaskCreate  $event
     * @return void
     */
    public function handle(TaskAction $event)
    {
        $activityinput = array_merge(
            [
                'text' => $event->getText(),
                'user_id' => Auth()->id(),
                'type' => 'task',
                'type_id' =>  $event->getTask()->id,
                'action' => $event->getAction()
            ]);
        
        Activity::create($activityinput);
    }
}
