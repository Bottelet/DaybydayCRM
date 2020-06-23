<?php

namespace App\Listeners;

use App\Events\LeadAction;
use App\Models\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Activity;
use Lang;
use App\Models\Lead;

class LeadActionLog
{
    /**
     * Action the event listener.
     *
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
                $text = __(':title was created by :creator and assigned to :assignee', [
                    'title' => $event->getLead()->title,
                    'creator' => $event->getLead()->creator->name,
                    'assignee' => $event->getLead()->user->name
                ]);
                break;
            case 'updated_status':
                $text = __('Lead status was updated by :username', [
                    'username' => Auth()->user()->name,
                ]);
                break;
            case 'updated_deadline':
                $text = __(':username updated the follow up time for this lead', [
                    'username' => Auth()->user()->name,
                ]);
                break;
            case 'updated_assign':
                $text = __(':username assigned lead to :assignee', [
                    'username' => Auth()->user()->name,
                    'assignee' => $event->getLead()->user->name
                ]);
                break;
            default:
                break;
        }

        activity("lead")
            ->performedOn($event->getLead())
            ->withProperties(['action' => $event->getAction()])
            ->log($text);
    }
}
