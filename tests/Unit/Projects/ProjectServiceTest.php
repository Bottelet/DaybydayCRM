<?php

namespace Tests\Unit\Projects;

use App\Models\Client;
use App\Models\Project;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use App\Services\Project\ProjectService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

#[CoversClass(ProjectService::class)]
class ProjectServiceTest extends AbstractTestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_creates_project_with_valid_data(): void
    {
        $service = $this->app->make(ProjectService::class);
        $user    = User::factory()->create();
        $client  = Client::factory()->create();
        $status  = Status::factory()->create(['source_type' => Project::class]);

        $project = $service->create([
            'title'              => 'P',
            'description'        => 'D',
            'user_assigned_id'   => $user->id,
            'deadline'           => '2026-02-01 12:00:00',
            'status_id'          => $status->id,
            'client_external_id' => $client->external_id,
        ], $user->id);

        $this->assertNotNull($project);
        $this->assertSame('P', $project->title);
        $this->assertSame($client->id, $project->client_id);
        $this->assertSame('2026-02-01', $project->deadline->format('Y-m-d'));
    }

    #[Test]
    public function it_assigns_user_to_project(): void
    {
        $service = $this->app->make(ProjectService::class);
        $user    = User::factory()->create();
        $project = Project::factory()->create();

        $service->assign($project, $user->id);

        $this->assertSame($user->id, $project->fresh()->user_assigned_id);
    }

    #[Test]
    public function it_updates_project_deadline(): void
    {
        $service = $this->app->make(ProjectService::class);
        $project = Project::factory()->create();

        $service->updateDeadline($project, '2026-03-01');

        $this->assertSame('2026-03-01', $project->fresh()->deadline->format('Y-m-d'));
    }

    #[Test]
    public function it_returns_null_when_client_external_id_missing(): void
    {
        $service = $this->app->make(ProjectService::class);
        $user    = User::factory()->create();
        $status  = Status::factory()->create(['source_type' => Project::class]);

        $result = $service->create([
            'client_external_id' => 'missing',
            'title'              => 'x',
            'description'        => 'x',
            'user_assigned_id'   => $user->id,
            'deadline'           => '2026-01-01',
            'status_id'          => $status->id,
        ], $user->id);

        $this->assertNull($result);
    }

    #[Test]
    public function it_returns_null_when_client_not_found(): void
    {
        $service = $this->app->make(ProjectService::class);
        $user    = User::factory()->create();
        $status  = Status::factory()->create(['source_type' => Project::class]);

        $result = $service->create([
            'client_external_id' => 'nonexistent-external-id',
            'title'              => 'x',
            'description'        => 'x',
            'user_assigned_id'   => $user->id,
            'deadline'           => '2026-01-01',
            'status_id'          => $status->id,
        ], $user->id);

        $this->assertNull($result);
    }

    #[Test]
    public function it_filters_out_tasks_without_assignees_from_show_data(): void
    {
        $service  = $this->app->make(ProjectService::class);
        $assignee = User::factory()->create();
        $client   = Client::factory()->create();
        $project  = Project::factory()->create([
            'client_id'        => $client->id,
            'user_assigned_id' => $assignee->id,
        ]);
        $taskUser = User::factory()->create();

        Task::factory()->create([
            'project_id'       => $project->id,
            'client_id'        => $project->client_id,
            'user_assigned_id' => $taskUser->id,
            'user_created_id'  => $assignee->id,
        ]);

        $removedAssignee = User::factory()->create();
        Task::factory()->create([
            'project_id'       => $project->id,
            'client_id'        => $project->client_id,
            'user_assigned_id' => $removedAssignee->id,
            'user_created_id'  => $assignee->id,
        ]);
        // Simulate a task pointing to a removed assignee (dangling FK on user relation lookup).
        $removedAssignee->delete();

        $prepared = $service->prepareShowCollaboratorsAndTasks($project);

        $this->assertCount(1, $prepared['tasks'], 'Only tasks with an assigned user should be kept.');
        $this->assertNotNull($prepared['tasks']->first()->user, 'The remaining task should have a loaded assignee.');
        $this->assertFalse($prepared['collaborators']->pluck('id')->contains($removedAssignee->id), 'Removed assignees should not be included as collaborators.');
        $this->assertCount(2, $prepared['collaborators'], 'Collaborators should include project assignee and task assignee.');
    }

    #[Test]
    public function it_prepares_unique_collaborators_when_assignee_and_task_user_are_same(): void
    {
        $service = $this->app->make(ProjectService::class);
        $user    = User::factory()->create();
        $client  = Client::factory()->create();
        $project = Project::factory()->create([
            'client_id'        => $client->id,
            'user_assigned_id' => $user->id,
        ]);

        Task::factory()->create([
            'project_id'       => $project->id,
            'client_id'        => $project->client_id,
            'user_assigned_id' => $user->id,
            'user_created_id'  => $user->id,
        ]);

        $prepared = $service->prepareShowCollaboratorsAndTasks($project);

        $this->assertCount(1, $prepared['collaborators']);
        $this->assertSame($user->id, $prepared['collaborators']->first()->id);
    }
}
