<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\Models\Task;
use App\Models\User;

class ProjectHeaderComposer
{
    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $project = $view->getData()["project"];
        
        $contact = $project->assignee;
        $client = $project->client;
        $contact_info = $client->contacts()->first();

        $view->with('contact', $contact);
        $view->with('contact_info', $contact_info);
        $view->with('client', $client);
    }
}
