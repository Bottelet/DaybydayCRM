<?php

namespace App\Services\Task;

use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Support\Carbon;

class TaskService
{
    public function create(array $validated, int $userId): Task
    {
        $clientId  = null;
        $projectId = null;

        if ( ! empty($validated['client_external_id'])) {
            $client   = Client::query()->where('external_id', $validated['client_external_id'])->first();
            $clientId = $client ? $client->id : null;
        }

        if ( ! empty($validated['project_external_id'])) {
            $project   = Project::query()->where('external_id', $validated['project_external_id'])->first();
            $projectId = $project ? $project->id : null;
        }

        return Task::query()->create([
            'title'            => $validated['title'],
            'description'      => clean($validated['description']),
            'user_assigned_id' => $validated['user_assigned_id'],
            'deadline'         => ! empty($validated['deadline']) ? Carbon::parse($validated['deadline'])->toDateString() : null,
            'status_id'        => $validated['status_id'],
            'user_created_id'  => $userId,
            'client_id'        => $clientId,
            'project_id'       => $projectId,
        ]);
    }

    public function assign(Task $task, int $userAssignedId): void
    {
        $task->user_assigned_id = $userAssignedId;
        $task->save();
    }

    public function updateDeadline(Task $task, string $deadline): void
    {
        $task->deadline = Carbon::parse($deadline)->toDateString();
        $task->save();
    }
}
