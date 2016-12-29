<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\Repositories\Lead\LeadRepositoryContract;

class LeadHeaderComposer
{
    /**
     * The task repository implementation.
     *
     * @var taskRepository
     */
    protected $lead;

    /**
     * Create a new profile composer.
     * LeadHeaderComposer constructor.
     * @param LeadRepositoryContract $lead
     */
    public function __construct(LeadRepositoryContract $lead)
    {
        $this->lead = $lead;
    }

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $lead = $this->lead->find($view->getData()['lead']['id']);
        /**
         * [User assigned the task]
         * @var contact
         */
       
        $contact = $lead->user;
        $client = $lead->client;
        
        $view->with('contact', $contact);
        $view->with('client', $client);
    }
}
