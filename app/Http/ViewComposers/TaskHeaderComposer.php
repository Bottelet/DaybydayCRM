<?php

namespace App\Http\ViewComposers;

use App\Models\Task;
use Illuminate\View\View;

class TaskHeaderComposer
{
    /**
     * Bind data to the view.
     *
     * @return void
     */
    public function compose(View $view)
    {
        $tasks = Task::findOrFail($view->getData()['tasks']['id']);

        /**
         * [User assigned the task]
         *
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
