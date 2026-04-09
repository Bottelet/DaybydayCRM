<?php

namespace Tests\Unit\Controllers\Task;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Permission;
use App\Models\Project;
use App\Models\Role;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[Group('authorization-fix')]
class TaskAuthorizationTest extends TestCase
{
    use DatabaseTransactions;

    private Task $task;

    private User $userWithPermission;

    private User $userWithoutPermission;

    protected function setUp(): void
    {
        parent::setUp();

        $this->task = factory(Task::class)->create();

        // Create or find task-delete permission
        $deletePermission = Permission::firstOrCreate(
            ['name' => 'task-delete'],
            [
                'display_name' => 'Delete task',
                'description' => 'Permission to delete task',
                'grouping' => 'task',
            ]
        );

        // Create role with task-delete permission
        $roleWithPermission = Role::create([
            'name' => 'task-deleter',
            'display_name' => 'Task Deleter',
            'description' => 'Can delete tasks',
            'external_id' => \Illuminate\Support\Str::uuid()->toString(),
        ]);
        $roleWithPermission->attachPermission($deletePermission);

        // Create role without task-delete permission
        $roleWithoutPermission = Role::create([
            'name' => 'task-viewer',
            'display_name' => 'Task Viewer',
            'description' => 'Cannot delete tasks',
            'external_id' => \Illuminate\Support\Str::uuid()->toString(),
        ]);

        // Create users
        $this->userWithPermission = factory(User::class)->create();
        $this->userWithPermission->attachRole($roleWithPermission);

        $this->userWithoutPermission = factory(User::class)->create();
        $this->userWithoutPermission->attachRole($roleWithoutPermission);

        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    #[Test]
    public function user_with_task_delete_permission_can_delete_task()
    {
        $this->actingAs($this->userWithPermission);

        $response = $this->json('DELETE', route('tasks.destroy', $this->task->external_id));

        $response->assertStatus(302); // Redirect on success
        $this->assertSoftDeleted('tasks', ['id' => $this->task->id]);
    }

    #[Test]
    public function user_without_task_delete_permission_cannot_delete_task()
    {
        $this->actingAs($this->userWithoutPermission);

        $response = $this->json('DELETE', route('tasks.destroy', $this->task->external_id));

        $response->assertStatus(403);
        $this->assertDatabaseHas('tasks', ['id' => $this->task->id, 'deleted_at' => null]);
    }

    #[Test]
    public function user_with_update_project_permission_can_update_task_project()
    {
        $project = factory(Project::class)->create(['client_id' => $this->task->client_id]);

        $roleWithPermission = Role::create([
            'name' => 'project-updater',
            'display_name' => 'Project Updater',
            'description' => 'Can update task project',
            'external_id' => \Illuminate\Support\Str::uuid()->toString(),
        ]);
        $updateProjectPermission = Permission::where('name', 'task-update-linked-project')->first();
        $roleWithPermission->attachPermission($updateProjectPermission);

        $user = factory(User::class)->create();
        $user->attachRole($roleWithPermission);
        $this->actingAs($user);

        $response = $this->json('PATCH', route('tasks.updateProject', $this->task->external_id), [
            'project_external_id' => $project->external_id,
        ]);

        $response->assertStatus(302);
        $this->assertEquals($project->id, $this->task->refresh()->project_id);
    }

    #[Test]
    public function user_without_update_project_permission_cannot_update_task_project()
    {
        $project = factory(Project::class)->create(['client_id' => $this->task->client_id]);

        $this->actingAs($this->userWithoutPermission);

        $response = $this->json('PATCH', route('tasks.updateProject', $this->task->external_id), [
            'project_external_id' => $project->external_id,
        ]);

        $response->assertStatus(403);
        $this->assertNull($this->task->refresh()->project_id);
    }

    #[Test]
    public function task_update_status_only_accepts_status_id_field()
    {
        $roleWithPermission = Role::create([
            'name' => 'status-updater',
            'display_name' => 'Status Updater',
            'description' => 'Can update status',
            'external_id' => \Illuminate\Support\Str::uuid()->toString(),
        ]);
        $statusPermission = Permission::where('name', 'task-update-status')->first();
        $roleWithPermission->attachPermission($statusPermission);

        $user = factory(User::class)->create();
        $user->attachRole($roleWithPermission);
        $this->actingAs($user);

        $newStatus = Status::typeOfTask()->where('id', '!=', $this->task->status_id)->first();
        $originalTitle = $this->task->title;
        $originalDescription = $this->task->description;

        $response = $this->json('PATCH', route('tasks.updateStatus', $this->task->external_id), [
            'status_id' => $newStatus->id,
            'title' => 'Malicious Title Change',
            'description' => 'Malicious Description Change',
            'user_assigned_id' => 999,
        ]);

        $this->task->refresh();

        $response->assertStatus(302);
        $this->assertEquals($newStatus->id, $this->task->status_id);
        // Verify mass assignment protection
        $this->assertEquals($originalTitle, $this->task->title);
        $this->assertEquals($originalDescription, $this->task->description);
        $this->assertNotEquals(999, $this->task->user_assigned_id);
    }
}
