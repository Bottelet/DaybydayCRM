<?php

namespace Tests\Feature\Controllers\Task;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Permission;
use App\Models\Project;
use App\Models\Role;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

#[Group('authorization-fix')]
class TaskAuthorizationTest extends AbstractTestCase
{
    use RefreshDatabase;

    private Task $task;

    private User $userWithPermission;

    private User $userWithoutPermission;

    protected function setUp(): void
    {
        parent::setUp();

        $this->task = Task::factory()->create();

        $deletePermission = Permission::firstOrCreate(
            ['name' => 'task-delete'],
            [
                'display_name' => 'Delete task',
                'description'  => 'Permission to delete task',
                'grouping'     => 'task',
            ]
        );

        $roleWithPermission = Role::create([
            'name'         => 'task-deleter',
            'display_name' => 'Task Deleter',
            'description'  => 'Can delete tasks',
            'external_id'  => Str::uuid()->toString(),
        ]);
        $roleWithPermission->attachPermission($deletePermission);

        $roleWithoutPermission = Role::create([
            'name'         => 'task-viewer',
            'display_name' => 'Task Viewer',
            'description'  => 'Cannot delete tasks',
            'external_id'  => Str::uuid()->toString(),
        ]);

        $this->userWithPermission = User::factory()->create();
        $this->userWithPermission->attachRole($roleWithPermission);

        $this->userWithoutPermission = User::factory()->create();
        $this->userWithoutPermission->attachRole($roleWithoutPermission);

        Cache::tags('role_user')->flush();

        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    #[Test]
    public function it_user_with_task_delete_permission_can_delete_task()
    {
        /* Arrange */
        $this->actingAs($this->userWithPermission);

        /* Act */
        $response = $this->json('DELETE', route('tasks.destroy', $this->task->external_id));

        /* Assert */
        $response->assertStatus(200);
        $this->assertSoftDeleted('tasks', ['id' => $this->task->id]);
    }

    #[Test]
    public function it_user_without_task_delete_permission_cannot_delete_task()
    {
        /* Arrange */
        $this->actingAs($this->userWithoutPermission);

        /* Act */
        $response = $this->json('DELETE', route('tasks.destroy', $this->task->external_id));

        /* Assert */
        $response->assertStatus(403);
        $this->assertDatabaseHas('tasks', ['id' => $this->task->id, 'deleted_at' => null]);
    }

    #[Test]
    public function it_user_with_update_project_permission_can_update_task_project()
    {
        /* Arrange */
        $project = Project::factory()->create(['client_id' => $this->task->client_id]);

        $roleWithPermission = Role::create([
            'name'         => 'project-updater',
            'display_name' => 'Project Updater',
            'description'  => 'Can update task project',
            'external_id'  => Str::uuid()->toString(),
        ]);
        $updateProjectPermission = Permission::firstOrCreate(['name' => 'task-update-linked-project'], [
            'display_name' => 'Update task linked project',
            'description'  => 'Can update task project',
            'grouping'     => 'task',
        ]);
        $roleWithPermission->attachPermission($updateProjectPermission);

        $user = User::factory()->create();
        $user->attachRole($roleWithPermission);
        $this->actingAs($user);

        /* Act */
        $response = $this->json('PATCH', route('tasks.updateProject', $this->task->external_id), [
            'project_external_id' => $project->external_id,
        ]);

        /* Assert */
        $response->assertStatus(302);
        $this->assertEquals($project->id, $this->task->refresh()->project_id);
    }

    #[Test]
    public function it_user_without_update_project_permission_cannot_update_task_project()
    {
        /* Arrange */
        $project = Project::factory()->create(['client_id' => $this->task->client_id]);

        $this->actingAs($this->userWithoutPermission);

        /* Act */
        $response = $this->json('PATCH', route('tasks.updateProject', $this->task->external_id), [
            'project_external_id' => $project->external_id,
        ]);

        /* Assert */
        $response->assertStatus(403);
        $this->assertNull($this->task->refresh()->project_id);
    }

    #[Test]
    public function it_task_update_status_only_accepts_status_id_field()
    {
        /* Arrange */
        $roleWithPermission = Role::create([
            'name'         => 'status-updater',
            'display_name' => 'Status Updater',
            'description'  => 'Can update status',
            'external_id'  => Str::uuid()->toString(),
        ]);
        $statusPermission = Permission::firstOrCreate(['name' => 'task-update-status'], [
            'display_name' => 'Update task status',
            'description'  => 'Can update task status',
            'grouping'     => 'task',
        ]);
        $roleWithPermission->attachPermission($statusPermission);

        $user = User::factory()->create();
        $user->attachRole($roleWithPermission);
        $this->actingAs($user);

        $newStatus = Status::factory()->create(['source_type' => \App\Models\Task::class]);
        while ($newStatus->id == $this->task->status_id) {
            $newStatus = Status::factory()->create(['source_type' => \App\Models\Task::class]);
        }
        $originalTitle       = $this->task->title;
        $originalDescription = $this->task->description;

        /* Act */
        $response = $this->json('PATCH', route('task.update.status', $this->task->external_id), [
            'status_id'        => $newStatus->id,
            'title'            => 'Malicious Title Change',
            'description'      => 'Malicious Description Change',
            'user_assigned_id' => 999,
        ]);

        /* Assert */
        $this->task->refresh();

        $response->assertStatus(200);
        $this->assertEquals($newStatus->id, $this->task->status_id);
        $this->assertEquals($originalTitle, $this->task->title);
        $this->assertEquals($originalDescription, $this->task->description);
        $this->assertNotEquals(999, $this->task->user_assigned_id);
    }
}
