<?php

namespace Tests\Feature\Tasks;

use App\Enums\PermissionName;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Lead;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

#[Group('security')]
#[Group('task-controller')]
class TaskSecurityTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected Task $task;

    protected User $unauthorizedUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->task = Task::factory()->create();

        $this->user = User::factory()->withRole('employee')->create();
        $this->actingAs($this->user);

        $this->unauthorizedUser = User::factory()->withRole('employee')->create();

        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    #[Test]
    public function it_authorized_user_can_delete_task()
    {
        /* Arrange */
        $this->withPermissions(PermissionName::TASK_DELETE);

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
        $this->withPermissions(PermissionName::TASK_UPDATE_STATUS);

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
        $this->withPermissions(PermissionName::TASK_UPDATE_STATUS);

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
        $this->withPermissions(PermissionName::TASK_UPDATE_STATUS);

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
        $this->withPermissions(PermissionName::TASK_UPDATE_STATUS);

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
        $this->withPermissions(PermissionName::TASK_UPDATE_STATUS);

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
