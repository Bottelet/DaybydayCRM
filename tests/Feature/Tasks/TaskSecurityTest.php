<?php

namespace Tests\Feature\Tasks;

use App\Enums\PermissionName;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Client;
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
        $response = $this->delete(route('tasks.destroy', $this->task->external_id), [], ['Accept' => 'application/json']);

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
        $response = $this->delete(route('tasks.destroy', $this->task->external_id), [], ['Accept' => 'application/json']);

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
        $response = $this->patch(route('task.update.status', $this->task->external_id), [
            'status_id'        => $newStatus->id,
            'user_assigned_id' => $this->user->id,
            'title'            => 'Hacked Title',
        ], ['Accept' => 'application/json']);

        /* Assert */
        $this->task->refresh();

        $response->assertStatus(200);
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
        $response = $this->patch(route('task.update.status', $this->task->external_id), [
            'statusExternalId' => 'invalid-uuid-12345',
        ], ['Accept' => 'application/json']);

        /* Assert */
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['statusExternalId' => 'The selected status external id is invalid.']);
    }

    #[Test]
    public function it_updates_status_via_ajax_with_valid_external_id()
    {
        /* Arrange */
        $this->withPermissions(PermissionName::TASK_UPDATE_STATUS);

        $newStatus = Status::factory()->create(['source_type' => Task::class]);

        /* Act */
        $response = $this->patch(route('task.update.status', $this->task->external_id), [
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
        $response = $this->patch(route('task.update.status', $this->task->external_id), [
            'status_id' => $leadStatus->id,
        ], ['Accept' => 'application/json']);

        /* Assert */
        $this->task->refresh();

        $this->assertEquals($originalStatus, $this->task->status_id);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['status_id' => 'Invalid status for task']);
    }

    #[Test]
    public function it_updates_status_rejects_nonexistent_status_id()
    {
        /* Arrange */
        $this->withPermissions(PermissionName::TASK_UPDATE_STATUS);

        $originalStatus = $this->task->status_id;

        /* Act */
        $response = $this->patch(route('task.update.status', $this->task->external_id), [
            'status_id' => 999999,
        ], ['Accept' => 'application/json']);

        /* Assert */
        $this->task->refresh();

        $this->assertEquals($originalStatus, $this->task->status_id);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['status_id' => 'The selected status id is invalid.']);
    }

    #[Test]
    public function it_unauthorized_user_cannot_update_status(): void
    {
        /* Arrange */
        $newStatus      = Status::factory()->create(['source_type' => Task::class]);
        $originalStatus = $this->task->status_id;

        /* Act */
        $response = $this->patch(route('task.update.status', $this->task->external_id), [
            'status_id' => $newStatus->id,
        ], ['Accept' => 'application/json']);

        /* Assert */
        $response->assertStatus(403);
        $this->task->refresh();
        $this->assertEquals($originalStatus, $this->task->status_id);
    }

    #[Test]
    public function it_updates_deadline_rejects_missing_deadline_date(): void
    {
        /* Arrange */
        $this->withPermissions(PermissionName::TASK_UPDATE_DEADLINE);

        /* Act */
        $response = $this->patch(route('task.update.deadline', $this->task->external_id), [
            'deadline_time' => '00:00',
        ], ['Accept' => 'application/json']);

        /* Assert */
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['deadline_date']);
    }

    #[Test]
    public function it_unauthorized_user_cannot_update_deadline(): void
    {
        /* Arrange */
        $originalDeadline = $this->task->refresh()->deadline;

        /* Act */
        $response = $this->patch(route('task.update.deadline', $this->task->external_id), [
            'deadline_date' => '2020-08-06',
        ], ['Accept' => 'application/json']);

        /* Assert */
        $response->assertStatus(403);
        $this->task->refresh();
        $this->assertEquals($originalDeadline, $this->task->deadline);
    }

    #[Test]
    public function it_creates_task_rejects_missing_required_fields(): void
    {
        /* Arrange */
        $this->withPermissions(PermissionName::TASK_CREATE);

        /* Act */
        $response = $this->post(route('tasks.store'), [], ['Accept' => 'application/json']);

        /* Assert */
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title', 'description', 'status_id', 'user_assigned_id', 'client_external_id']);
    }

    #[Test]
    public function it_unauthorized_user_cannot_create_task(): void
    {
        /* Arrange */
        $client = Client::factory()->create();
        $status = Status::factory()->create(['source_type' => Task::class]);

        /* Act */
        $response = $this->post(route('tasks.store'), [
            'title'              => 'Unauthorized Task',
            'description'        => 'This should not be created',
            'status_id'          => $status->id,
            'user_assigned_id'   => $this->user->id,
            'client_external_id' => $client->external_id,
        ], ['Accept' => 'application/json']);

        /* Assert */
        $response->assertStatus(403);
        $this->assertDatabaseMissing('tasks', ['title' => 'Unauthorized Task']);
    }

    #[Test]
    public function it_updates_task_rejects_missing_title(): void
    {
        /* Arrange */
        $this->withPermissions(PermissionName::TASK_UPDATE);

        /* Act */
        $response = $this->put(route('tasks.update', $this->task->external_id), [
            'description' => 'Updated description',
        ], ['Accept' => 'application/json']);

        /* Assert */
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title']);
    }

    #[Test]
    public function it_unauthorized_user_cannot_update_task(): void
    {
        /* Arrange */
        $originalTitle = $this->task->title;

        /* Act */
        $response = $this->put(route('tasks.update', $this->task->external_id), [
            'title'       => 'Hacked Title',
            'description' => 'Hacked description',
        ], ['Accept' => 'application/json']);

        /* Assert */
        $response->assertStatus(403);
        $this->task->refresh();
        $this->assertEquals($originalTitle, $this->task->title);
    }

    #[Test]
    public function it_authorized_user_can_update_assignee()
    {
        /* Arrange */
        $this->withPermissions(PermissionName::TASK_ASSIGN);
        $newAssignee = User::factory()->create();

        /* Act */
        $response = $this->patch(route('task.update.assignee', $this->task->external_id), [
            'user_assigned_id' => $newAssignee->id,
        ], ['Accept' => 'application/json']);

        /* Assert */
        $response->assertStatus(200);
        $this->task->refresh();
        $this->assertEquals($newAssignee->id, $this->task->user_assigned_id);
    }

    #[Test]
    public function it_unauthorized_user_cannot_update_assign()
    {
        /* Arrange */
        $originalAssignee = $this->task->user_assigned_id;
        $newAssignee      = User::factory()->create();

        /* Act */
        $response = $this->patch(route('task.update.assignee', $this->task->external_id), [
            'user_assigned_id' => $newAssignee->id,
        ], ['Accept' => 'application/json']);

        /* Assert */
        $response->assertStatus(403);
        $this->task->refresh();
        $this->assertEquals($originalAssignee, $this->task->user_assigned_id);
    }

    #[Test]
    public function it_updates_assignee_rejects_missing_user_assigned_id(): void
    {
        /* Arrange */
        $this->withPermissions(PermissionName::TASK_ASSIGN);

        /* Act */
        $response = $this->patch(route('task.update.assignee', $this->task->external_id), [], ['Accept' => 'application/json']);

        /* Assert */
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['user_assigned_id']);
    }
}
