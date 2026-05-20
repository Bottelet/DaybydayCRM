<?php

namespace Tests\Feature\Tasks;

use App\Enums\PermissionName;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Project;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

#[Group('authorization-fix')]
class TaskAuthorizationTest extends AbstractTestCase
{
    use RefreshDatabase;

    private Task $task;

    protected function setUp(): void
    {
        parent::setUp();

        $this->task = Task::factory()->create();
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    #[Test]
    public function it_user_with_task_delete_permission_can_delete_task()
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
    public function it_user_without_task_delete_permission_cannot_delete_task()
    {
        /* Arrange */
        $this->actingAs(User::factory()->create());

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
        $this->withPermissions(PermissionName::TASK_UPDATE_LINKED_PROJECT);
        $project = Project::factory()->create(['client_id' => $this->task->client_id]);

        /* Act */
        $response = $this->json('PATCH', route('tasks.updateProject', $this->task->external_id), [
            'project_external_id' => $project->external_id,
        ]);

        /* Assert */
        $response->assertStatus(200);
        $this->assertEquals($project->id, $this->task->refresh()->project_id);
    }

    #[Test]
    public function it_user_without_update_project_permission_cannot_update_task_project()
    {
        /* Arrange */
        $this->actingAs(User::factory()->create());
        $project = Project::factory()->create(['client_id' => $this->task->client_id]);

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
        $this->withPermissions(PermissionName::TASK_UPDATE_STATUS);

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
