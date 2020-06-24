<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\Models\Task;

class TaskHeaderComposer
{
    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $tasks = Task::findOrFail($view->getData()['tasks']['id']);
        
        /**
         * [User assigned the task]
         * @var contact
         */
       
        $contact = $tasks->user;
        $client = $tasks->client;
        $contact_info = $client->contacts()->first();

        $view->with('contact', $contact);
        $view->with('contact_info', $contact_info);
        $view->with('client', $client);
    }
}
