<?php

namespace Tests\Feature\Controllers\Task;

use App\Models\Client;
use App\Models\Permission;
use App\Models\Project;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TasksControllerTest extends AbstractTestCase
{
    use RefreshDatabase;

    private $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $role = \App\Models\Role::firstOrCreate(['name' => 'employee'], ['display_name' => 'Employee']);
        $this->user->attachRole($role);
        $this->client = Client::factory()->create();
    }

    #[Test]
    #[Group('junie_repaired')]
    public function can_create_task()
    {
        // Ensure user has permission to create tasks
        $permission = Permission::firstOrCreate(['name' => 'task-create']);
        $this->user->roles->first()->attachPermission($permission);
        $this->user = $this->user->fresh();
        $this->actingAs($this->user);
        Cache::tags('role_user')->flush();

        $response = $this->json('POST', route('tasks.store'), [
            'title' => 'Task test',
            'description' => 'This is a description',
            'status_id' => Status::factory()->create(['source_type' => Task::class])->id,
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
    public function it_can_add_project_on_task()
    {
        // Ensure user has permission to update task project
        $permission = Permission::firstOrCreate(['name' => 'task-update-linked-project']);
        $this->user->roles->first()->attachPermission($permission);
        $this->user = $this->user->fresh();
        $this->actingAs($this->user);
        Cache::tags('role_user')->flush();

        $project = Project::factory()->create();
        $task = Task::factory()->create();

        $this->assertNull($task->project_id);
        $response = $this->json('POST', route('tasks.update.project', $task->external_id), [
            'project_external_id' => $project->external_id,
        ]);

        $this->assertNotNull($task->refresh()->project_id);
    }

    #[Test]
    public function it_can_update_assignee()
    {
        // Ensure user has permission to assign tasks
        $permission = Permission::firstOrCreate(['name' => 'can-assign-new-user-to-task']);
        $this->user->roles->first()->attachPermission($permission);
        $this->user = $this->user->fresh();
        $this->actingAs($this->user);
        Cache::tags('role_user')->flush();

        $task = Task::factory()->create();
        $this->assertNotEquals($task->user_assigned_id, $this->user->id);

        $response = $this->json('PATCH', route('task.update.assignee', $task->external_id), [
            'user_assigned_id' => $this->user->id,
        ]);

        $this->assertEquals($this->user->id, $task->refresh()->user_assigned_id);
    }

    #[Test]
    public function it_can_update_status()
    {
        $task = Task::factory()->create();
        $status = Status::factory()->create(['source_type' => Task::class]);

        $this->assertNotEquals($task->status_id, $status->id);

        // Ensure user has permission
        $permission = Permission::firstOrCreate(['name' => 'task-update-status']);
        $this->user->roles->first()->attachPermission($permission);
        $this->user = $this->user->fresh();
        $this->actingAs($this->user);
        Cache::tags('role_user')->flush();

        $response = $this->json('PATCH', route('task.update.status', $task->external_id), [
            'status_id' => $status->id,
        ]);

        $this->assertEquals($task->refresh()->status_id, $status->id);
    }

    #[Test]
    public function it_can_update_deadline_for_task()
    {
        $task = Task::factory()->create();

        // Ensure user has permission
        $permission = Permission::firstOrCreate(['name' => 'task-update-deadline']);
        $this->user->roles->first()->attachPermission($permission);
        $this->user = $this->user->fresh();
        $this->actingAs($this->user);
        Cache::tags('role_user')->flush();

        $response = $this->json('PATCH', route('task.update.deadline', $task->external_id), [
            'deadline_date' => '2020-08-06',
            'deadline_time' => '00:00',
        ]);

        $this->assertEquals(Carbon::parse('2020-08-06')->toDateString(), Carbon::parse($task->refresh()->deadline)->toDateString());
    }

    #[Test]
    public function it_can_list_tasks()
    {
        Task::factory()->create();

        $error = $this->json('GET', route('tasks.data'))
            ->assertSuccessful()
            ->json('error');
        $this->assertNull($error);
    }
}
