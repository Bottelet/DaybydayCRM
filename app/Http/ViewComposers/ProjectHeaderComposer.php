<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;

class ProjectHeaderComposer
{
    /**
     * Bind data to the view.
     *
     * @return void
     */
    public function compose(View $view)
    {
        $project = $view->getData()['project'];

        $contact      = $project->assignee;
        $client       = $project->client;
        $contact_info = $client->contacts()->first();

        $view->with('contact', $contact);
        $view->with('contact_info', $contact_info);
        $view->with('client', $client);
    }
}
