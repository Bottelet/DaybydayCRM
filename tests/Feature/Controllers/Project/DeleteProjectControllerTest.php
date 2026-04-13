<?php

namespace Tests\Feature\Controllers\Project;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Permission;
use App\Models\Project;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

class DeleteProjectControllerTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected $user;

    private $project;

    private $task;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $role       = Role::firstOrCreate(['name' => 'employee']);
        $permission = Permission::firstOrCreate(['name' => 'project-delete']);
        $role->attachPermission($permission);
        $this->user->attachRole($role);

        // Explicitly clear both permission caches
        Cache::tags('role_user')->flush();
        Cache::tags('permission_role')->flush();
        $this->user = $this->user->fresh();

        $this->actingAs($this->user);

        $this->project = Project::factory()->create();
        $this->task    = Task::factory()->create([
            'project_id' => $this->project->id,
        ]);
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    #[Test]
    public function it_deletes_project()
    {
        $response = $this->json('DELETE', route('projects.destroy', $this->project->external_id));

        $response->assertStatus(200);
        $this->assertSoftDeleted('projects', ['id' => $this->project->id]);
    }

    #[Test]
    public function it_deletes_tasks_if_flag_given()
    {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
        ]);

        $response = $this->json('DELETE', route('projects.destroy', $this->project->external_id), [
            'delete_tasks' => 'on',
        ]);

        $response->assertStatus(200);
        $this->assertSoftDeleted('projects', ['id' => $this->project->id]);
        $this->assertSoftDeleted('tasks', ['id' => $this->task->id]);
        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }

    #[Test]
    public function it_removes_project_id_from_task_if_flag_not_given()
    {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
        ]);

        $response = $this->json('DELETE', route('projects.destroy', $this->project->external_id));

        $response->assertStatus(200);

        $this->assertNull($this->task->refresh()->deleted_at);
        $this->assertNull($this->task->refresh()->project_id);

        $this->assertNull($task->refresh()->deleted_at);
        $this->assertNull($task->refresh()->project_id);
    }

    #[Test]
    public function it_can_delete_project_if_there_is_no_tasks()
    {
        $project  = Project::factory()->create();
        $response = $this->json('DELETE', route('projects.destroy', $project->external_id));

        $response->assertStatus(200);
        $this->assertnotNull($project->refresh()->deleted_at);
    }
}
