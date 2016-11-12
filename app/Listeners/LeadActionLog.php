<?php

namespace App\Listeners;

use App\Events\LeadAction;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Activity;

class LeadActionLog
{
    /**
     * Action the event listener.
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
     * @param  LeadAction  $event
     * @return void
     */
    public function handle(LeadAction $event)
    {
        switch ($event->getAction()) {
            case 'created':
                $text = $event->getLead()->title .
                ' was created by '. $event->getLead()->createdBy->name .
                ' and assigned to ' . $event->getLead()->assignee->name;
                break;
            case 'updated_status':
                $text = 'Lead was completed by '. Auth()->user()->name;
                break;
            case 'updated_deadline':
                $text = Auth()->user()->name.' updated the deadline for this lead';
                break;
            case 'updated_assign':
                $text = auth()->user()->name.' assigned lead to '. $event->getLead()->assignee->name;
                break;
            default:
                break;
        }

        $activityinput = array_merge(
            [
                'text' => $text,
                'user_id' => Auth()->id(),
                'type' => 'lead',
                'type_id' =>  $event->getLead()->id,
                'action' => $event->getAction()
            ]);
        
        Activity::create($activityinput);
    }
}
