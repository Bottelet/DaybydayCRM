<?php

namespace Tests\Unit\Controllers\Task;

use App\Models\Client;
use App\Models\Project;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TasksControllerTest extends TestCase
{
    use DatabaseTransactions, WithoutMiddleware;

    private $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();
        $this->client = factory(Client::class)->create();
    }

    #[Test]
    #[Group('junie_repaired')]
    public function can_create_task()
    {
        $this->markTestIncomplete('failure repaired by junie');
        $response = $this->json('POST', route('tasks.store'), [
            'title' => 'Task test',
            'description' => 'This is a description',
            'status_id' => factory(Status::class)->create(['source_type' => Task::class])->id,
            'user_assigned_id' => $this->user->id,
            'user_created_id' => $this->user->id,
            'client_external_id' => $this->client->external_id,
            'deadline' => '2020-01-01',
        ]);

        $tasks = Task::where('user_assigned_id', $this->user->id);

        $this->assertCount(1, $tasks->get());
        $this->assertEquals($response->getData()->task_external_id, $tasks->first()->external_id);
    }

    #[Test]
    public function can_add_project_on_task()
    {
        $project = factory(Project::class)->create();
        $task = factory(Task::class)->create();

        $this->assertNull($task->project_id);
        $response = $this->json('POST', route('tasks.update.project', $task->external_id), [
            'project_external_id' => $project->external_id,
        ]);

        $this->assertNotNull($task->refresh()->project_id);
    }

    #[Test]
    public function can_update_assignee()
    {
        $task = factory(Task::class)->create();
        $this->assertNotEquals($task->user_assigned_id, $this->user->id);

        $response = $this->json('PATCH', route('task.update.assignee', $task->external_id), [
            'user_assigned_id' => $this->user->id,
        ]);

        $this->assertEquals($task->refresh()->user_assigned_id, $this->user->id);
    }

    #[Test]
    public function can_update_status()
    {
        $task = factory(Task::class)->create();
        $status = factory(Status::class)->create(['source_type' => Task::class]);

        $this->assertNotEquals($task->status_id, $status->id);
        
        // Ensure user has permission
        $permission = \App\Models\Permission::firstOrCreate(['name' => 'task-update-status']);
        $this->user->roles->first()->attachPermission($permission);
        \Illuminate\Support\Facades\Cache::tags('role_user')->flush();

        $response = $this->json('PATCH', route('task.update.status', $task->external_id), [
            'status_id' => $status->id,
        ]);

        $this->assertEquals($task->refresh()->status_id, $status->id);
    }

    #[Test]
    public function can_update_deadline_for_task()
    {
        $task = factory(Task::class)->create();
        
        // Ensure user has permission
        $permission = \App\Models\Permission::firstOrCreate(['name' => 'task-update-deadline']);
        $this->user->roles->first()->attachPermission($permission);
        \Illuminate\Support\Facades\Cache::tags('role_user')->flush();

        $response = $this->json('PATCH', route('task.update.deadline', $task->external_id), [
            'deadline_date' => '2020-08-06',
            'deadline_time' => '00:00',
        ]);

        $this->assertEquals(Carbon::parse('2020-08-06')->toDateString(), Carbon::parse($task->refresh()->deadline)->toDateString());
    }

    #[Test]
    public function can_list_tasks()
    {
        factory(Task::class)->create();

        $error = $this->json('GET', route('tasks.data'))
            ->assertSuccessful()
            ->json('error');
        $this->assertNull($error);
    }
}
