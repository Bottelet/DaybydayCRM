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
    protected $leads;

    /**
     * Create a new profile composer.
     *
     * @param  taskRepository  $leads
     * @return void
     */
    public function __construct(LeadRepositoryContract $leads)
    {
        $this->leads = $leads;
    }

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $leads = $this->leads->find($view->getData()['leads']['id']);
        /**
         * [User assigned the task]
         * @var contact
         */
       
        $contact = $leads->assignee;
        $client = $leads->clientAssignee;
        
        $view->with('contact', $contact);
        $view->with('client', $client);
    }
}
