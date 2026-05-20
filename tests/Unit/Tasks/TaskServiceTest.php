<?php

namespace Tests\Unit\Tasks;

use App\Models\Client;
use App\Models\Project;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use App\Services\Task\TaskService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

#[CoversClass(TaskService::class)]
class TaskServiceTest extends AbstractTestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_creates_task_with_valid_data(): void
    {
        $service = new TaskService();
        $user    = User::factory()->create();
        $client  = Client::factory()->create();
        $project = Project::factory()->create(['client_id' => $client->id]);
        $status  = Status::factory()->create(['source_type' => Task::class]);

        $task = $service->create([
            'title'               => 'T',
            'description'         => 'D',
            'user_assigned_id'    => $user->id,
            'deadline'            => '2026-02-01 12:00:00',
            'status_id'           => $status->id,
            'client_external_id'  => $client->external_id,
            'project_external_id' => $project->external_id,
        ], $user->id);

        $this->assertNotNull($task);
        $this->assertSame('T', $task->title);
        $this->assertSame($client->id, $task->client_id);
        $this->assertSame($project->id, $task->project_id);
        $this->assertSame('2026-02-01', $task->deadline->format('Y-m-d'));
    }

    #[Test]
    public function it_assigns_user_to_task(): void
    {
        $service = new TaskService();
        $user    = User::factory()->create();
        $task    = Task::factory()->create();

        $service->assign($task, $user->id);

        $this->assertSame($user->id, $task->fresh()->user_assigned_id);
    }

    #[Test]
    public function it_updates_task_deadline(): void
    {
        $service = new TaskService();
        $task    = Task::factory()->create();

        $service->updateDeadline($task, '2026-02-03 13:00:00');

        $this->assertSame('2026-02-03', $task->fresh()->deadline->format('Y-m-d'));
    }
}
