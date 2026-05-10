<?php

namespace Tests\Feature\Controllers\Task;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Lead;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

#[Group('security')]
#[Group('task-controller')]
class TaskSecurityTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected $task;

    protected $unauthorizedUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->task = Task::factory()->create();

        $this->user = User::factory()->withRole('employee')->create();
        $this->actingAs($this->user);

        $this->unauthorizedUser = User::factory()->withRole('employee')->create();

        Cache::tags('role_user')->flush();

        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    #[Test]
    public function it_authorized_user_can_delete_task()
    {
        /* Arrange */
        $permission = Permission::firstOrCreate(['name' => 'task-delete']);
        $this->user->roles->first()->attachPermission($permission);
        $this->user = $this->user->fresh();

        Cache::tags('role_user')->flush();

        /* Act */
        $response = $this->json('DELETE', route('tasks.destroy', $this->task->external_id));

        /* Assert */
        $response->assertStatus(200);
        $this->assertSoftDeleted('tasks', ['id' => $this->task->id]);
    }

    #[Test]
    public function it_unauthorized_user_cannot_delete_task()
    {
        /* Arrange */
        $this->actingAs($this->unauthorizedUser);

        /* Act */
        $response = $this->json('DELETE', route('tasks.destroy', $this->task->external_id));

        /* Assert */
        $response->assertStatus(403);
        $this->assertDatabaseHas('tasks', ['id' => $this->task->id, 'deleted_at' => null]);
    }

    #[Test]
    public function it_updates_status_only_accepts_status_id_field()
    {
        /* Arrange */
        $permission = Permission::firstOrCreate(['name' => 'task-update-status']);
        $this->user->roles->first()->attachPermission($permission);
        Cache::tags('role_user')->flush();

        $newStatus        = Status::factory()->create(['source_type' => Task::class]);
        $originalAssignee = $this->task->user_assigned_id;

        /* Act */
        $response = $this->json('PATCH', route('task.update.status', $this->task->external_id), [
            'status_id'        => $newStatus->id,
            'user_assigned_id' => $this->user->id,
            'title'            => 'Hacked Title',
        ]);

        /* Assert */
        $this->task->refresh();

        $this->assertEquals($newStatus->id, $this->task->status_id);

        $this->assertEquals($originalAssignee, $this->task->user_assigned_id);

        $this->assertNotEquals('Hacked Title', $this->task->title);
    }

    #[Test]
    public function it_updates_status_with_invalid_status_external_id_returns_error()
    {
        /* Arrange */
        $permission = Permission::firstOrCreate(['name' => 'task-update-status']);
        $this->user->roles->first()->attachPermission($permission);
        Cache::tags('role_user')->flush();

        /* Act */
        $response = $this->json('PATCH', route('task.update.status', $this->task->external_id), [
            'statusExternalId' => 'invalid-uuid-12345',
        ], ['X-Requested-With' => 'XMLHttpRequest']);

        /* Assert */
        $response->assertStatus(400)
            ->assertJson(['error' => 'Invalid status external id']);
    }

    #[Test]
    public function it_updates_status_via_ajax_with_valid_external_id()
    {
        /* Arrange */
        $permission = Permission::firstOrCreate(['name' => 'task-update-status']);
        $this->user->roles->first()->attachPermission($permission);
        Cache::tags('role_user')->flush();

        $newStatus = Status::factory()->create(['source_type' => Task::class]);

        /* Act */
        $response = $this->json('PATCH', route('task.update.status', $this->task->external_id), [
            'statusExternalId' => $newStatus->external_id,
        ], ['X-Requested-With' => 'XMLHttpRequest']);

        /* Assert */
        $this->task->refresh();
        $this->assertEquals($newStatus->id, $this->task->status_id);
    }

    #[Test]
    public function it_updates_status_rejects_invalid_status_type()
    {
        /* Arrange */
        $permission = Permission::firstOrCreate(['name' => 'task-update-status']);
        $this->user->roles->first()->attachPermission($permission);
        Cache::tags('role_user')->flush();

        $leadStatus     = Status::factory()->create(['source_type' => Lead::class]);
        $originalStatus = $this->task->status_id;

        /* Act */
        $response = $this->json('PATCH', route('task.update.status', $this->task->external_id), [
            'status_id' => $leadStatus->id,
        ]);

        /* Assert */
        $this->task->refresh();

        $this->assertEquals($originalStatus, $this->task->status_id);

        $response->assertStatus(400);
        $response->assertJson(['error' => 'Invalid status for task']);
    }

    #[Test]
    public function it_updates_status_rejects_nonexistent_status_id()
    {
        /* Arrange */
        $permission = Permission::firstOrCreate(['name' => 'task-update-status']);
        $this->user->roles->first()->attachPermission($permission);
        Cache::tags('role_user')->flush();

        $originalStatus = $this->task->status_id;

        /* Act */
        $response = $this->json('PATCH', route('task.update.status', $this->task->external_id), [
            'status_id' => 999999,
        ]);

        /* Assert */
        $this->task->refresh();

        $this->assertEquals($originalStatus, $this->task->status_id);

        $response->assertStatus(400);
        $response->assertJson(['error' => 'Invalid status for task']);
    }
}
