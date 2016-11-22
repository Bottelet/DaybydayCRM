<?php

namespace App\Listeners;

use App\Events\LeadAction;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Activity;
use Lang;
use App\Models\Leads;

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
                $text = Lang::get('misc.log.lead.created', [
                    'title' => $event->getLead()->title,
                    'creator' => $event->getLead()->createdBy->name,
                    'assignee' => $event->getLead()->assignee->name
                ]);
                break;
            case 'updated_status':
                $text = Lang::get('misc.log.lead.status', [
                    'username' => Auth()->user()->name,
                ]);
                break;
            case 'updated_deadline':
                $text = Lang::get('misc.log.lead.deadline', [
                    'username' => Auth()->user()->name,
                ]);
                break;
            case 'updated_assign':
                $text = Lang::get('misc.log.lead.assign', [
                    'username' => Auth()->user()->name,
                    'assignee' => $event->getLead()->assignee->name
                ]);
                break;
            default:
                break;
        }

        $activityinput = array_merge(
            [
                'text' => $text,
                'user_id' => Auth()->id(),
                'type' => Leads::class,
                'type_id' =>  $event->getLead()->id,
                'action' => $event->getAction()
            ]
        );
        
        Activity::create($activityinput);
    }
}
