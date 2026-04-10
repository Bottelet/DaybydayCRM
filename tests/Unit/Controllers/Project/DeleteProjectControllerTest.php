<?php

namespace Tests\Unit\Controllers\Project;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Permission;
use App\Models\Project;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeleteProjectControllerTest extends TestCase
{
    use DatabaseTransactions;

    private $project;

    private $task;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'employee']);
        $permission = Permission::firstOrCreate(['name' => 'project-delete']);
        $role->attachPermission($permission);
        $this->user->attachRole($role);
        $this->actingAs($this->user);

        $this->project = Project::factory()->create();
        $this->task = Task::factory()->create([
            'project_id' => $this->project->id,
        ]);
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    #[Test]
    public function delete_project()
    {
        $this->json('DELETE', route('projects.destroy', $this->project->external_id));

        $this->assertSoftDeleted('projects', ['id' => $this->project->id]);
    }

    #[Test]
    public function delete_tasks_if_flag_given()
    {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
        ]);

        $this->json('DELETE', route('projects.destroy', $this->project->external_id), [
            'delete_tasks' => 'on',
        ]);

        $this->assertSoftDeleted('projects', ['id' => $this->project->id]);
        $this->assertSoftDeleted('tasks', ['id' => $this->task->id]);
        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }

    #[Test]
    public function remove_project_id_from_task_if_flag_not_given()
    {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
        ]);

        $this->json('DELETE', route('projects.destroy', $this->project->external_id));

        $this->assertNull($this->task->refresh()->deleted_at);
        $this->assertNull($this->task->refresh()->project_id);

        $this->assertNull($task->refresh()->deleted_at);
        $this->assertNull($task->refresh()->project_id);
    }

    #[Test]
    public function can_delete_project_if_there_is_no_tasks()
    {
        $project = Project::factory()->create();
        $this->json('DELETE', route('projects.destroy', $project->external_id));

        $this->assertnotNull($project->refresh()->deleted_at);
    }
}
