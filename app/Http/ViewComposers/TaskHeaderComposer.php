<?php

namespace App\Http\ViewComposers;

use App\Models\Task;
use Illuminate\Contracts\View\View;

class TaskHeaderComposer
{
    /**
     * Bind data to the view.
     *
     * @return void
     */
    public function compose(View $view)
    {
        $data = $view->getData();

        // The view passes either a Task model directly or an array with 'id'.
        $task = $data['tasks'] ?? null;

        if ($task instanceof Task) {
            $taskModel = $task;
        } elseif (is_array($task) && isset($task['id'])) {
            $taskModel = Task::find($task['id']);
        } else {
            $taskModel = null;
        }

        $contact      = $taskModel?->user;
        $client       = $taskModel?->client;
        $contact_info = $client?->contacts()->first();

        $view->with('contact', $contact);
        $view->with('contact_info', $contact_info);
        $view->with('client', $client);
    }
}
