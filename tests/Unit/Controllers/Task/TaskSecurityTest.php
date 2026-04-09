<?php

namespace Tests\Unit\Controllers\Task;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Lead;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[Group('security')]
#[Group('task-controller')]
class TaskSecurityTest extends TestCase
{
    use DatabaseTransactions;

    protected $task;

    protected $unauthorizedUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->task = factory(Task::class)->create();

        // Create and authenticate a user with default role
        $this->user = factory(User::class)->create();
        $role = Role::where('name', 'employee')->first();
        $this->user->attachRole($role);
        $this->actingAs($this->user);

        // Create a user without task-delete permission
        $this->unauthorizedUser = factory(User::class)->create();
        $this->unauthorizedUser->attachRole($role);

        // Disable CSRF middleware for all tests
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    #[Test]
    public function authorized_user_can_delete_task()
    {
        // Give user permission to delete tasks
        $permission = Permission::firstOrCreate(['name' => 'task-delete']);
        $this->user->roles->first()->attachPermission($permission);

        $response = $this->json('DELETE', route('tasks.destroy', $this->task->external_id));

        $response->assertRedirect();
        $this->assertSoftDeleted('tasks', ['id' => $this->task->id]);
    }

    #[Test]
    public function unauthorized_user_cannot_delete_task()
    {
        $this->actingAs($this->unauthorizedUser);

        $response = $this->json('DELETE', route('tasks.destroy', $this->task->external_id));

        $response->assertRedirect();
        $response->assertSessionHas('flash_message_warning');
        $this->assertDatabaseHas('tasks', ['id' => $this->task->id, 'deleted_at' => null]);
    }

    #[Test]
    public function update_status_only_accepts_status_id_field()
    {
        $permission = Permission::firstOrCreate(['name' => 'task-update-status']);
        $this->user->roles->first()->attachPermission($permission);

        $newStatus = factory(Status::class)->create(['source_type' => Task::class]);
        $originalAssignee = $this->task->user_assigned_id;

        // Use PATCH (route is PATCH)
        $response = $this->json('PATCH', route('task.update.status', $this->task->external_id), [
            'status_id' => $newStatus->id,
            'user_assigned_id' => $this->user->id, // This should be ignored
            'title' => 'Hacked Title', // This should be ignored
        ]);

        $this->task->refresh();

        // Status should be updated
        $this->assertEquals($newStatus->id, $this->task->status_id);

        // But user_assigned_id should NOT be changed (mass assignment protection)
        $this->assertEquals($originalAssignee, $this->task->user_assigned_id);

        // Title should not be changed
        $this->assertNotEquals('Hacked Title', $this->task->title);
    }

    #[Test]
    public function update_status_with_invalid_status_external_id_returns_error()
    {
        $permission = Permission::firstOrCreate(['name' => 'task-update-status']);
        $this->user->roles->first()->attachPermission($permission);

        // Use PATCH (route is PATCH)
        $response = $this->json('PATCH', route('task.update.status', $this->task->external_id), [
            'statusExternalId' => 'invalid-uuid-12345',
        ], ['X-Requested-With' => 'XMLHttpRequest']);

        $response->assertStatus(400)
            ->assertJson(['error' => __('Invalid status')]);
    }

    #[Test]
    public function update_status_via_ajax_with_valid_external_id()
    {
        $permission = Permission::firstOrCreate(['name' => 'task-update-status']);
        $this->user->roles->first()->attachPermission($permission);

        $newStatus = factory(Status::class)->create(['source_type' => Task::class]);

        // Use PATCH (route is PATCH)
        $response = $this->json('PATCH', route('task.update.status', $this->task->external_id), [
            'statusExternalId' => $newStatus->external_id,
        ], ['X-Requested-With' => 'XMLHttpRequest']);

        $this->task->refresh();
        $this->assertEquals($newStatus->id, $this->task->status_id);
    }

    #[Test]
    public function update_status_rejects_invalid_status_type()
    {
        $permission = Permission::firstOrCreate(['name' => 'task-update-status']);
        $this->user->roles->first()->attachPermission($permission);

        // Create a status that belongs to a different type (Lead instead of Task)
        $leadStatus = factory(Status::class)->create(['source_type' => Lead::class]);
        $originalStatus = $this->task->status_id;

        // Use PATCH (route is PATCH)
        $response = $this->json('PATCH', route('task.update.status', $this->task->external_id), [
            'status_id' => $leadStatus->id,
        ]);

        $this->task->refresh();

        // Status should NOT be changed because it's not a valid task status
        $this->assertEquals($originalStatus, $this->task->status_id);

        // Should show warning message
        $response->assertRedirect();
        $response->assertSessionHas('flash_message_warning', __('Invalid status for task'));
    }

    #[Test]
    public function update_status_rejects_nonexistent_status_id()
    {
        $permission = Permission::firstOrCreate(['name' => 'task-update-status']);
        $this->user->roles->first()->attachPermission($permission);

        $originalStatus = $this->task->status_id;

        // Use PATCH (route is PATCH)
        $response = $this->json('PATCH', route('task.update.status', $this->task->external_id), [
            'status_id' => 999999,
        ]);

        $this->task->refresh();

        // Status should NOT be changed
        $this->assertEquals($originalStatus, $this->task->status_id);

        // Should show warning message
        $response->assertRedirect();
        $response->assertSessionHas('flash_message_warning', __('Invalid status for task'));
    }
}
