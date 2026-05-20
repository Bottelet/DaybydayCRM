<?php

namespace Tests\Feature\Projects;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        /* Arrange */
        $this->user = User::factory()->create();
        $this->asOwner();
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
        /* Arrange */
        /* Act */
        $response = $this->json('DELETE', route('projects.destroy', $this->project->external_id));

        /* Assert */
        $response->assertStatus(200);
        $this->assertSoftDeleted('projects', ['id' => $this->project->id]);
    }

    #[Test]
    public function it_deletes_tasks_if_flag_given()
    {
        /* Arrange */
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
        ]);

        /* Act */
        $response = $this->json('DELETE', route('projects.destroy', $this->project->external_id), [
            'delete_tasks' => 'on',
        ]);

        /* Assert */
        $response->assertStatus(200);
        $this->assertSoftDeleted('projects', ['id' => $this->project->id]);
        $this->assertSoftDeleted('tasks', ['id' => $this->task->id]);
        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }

    #[Test]
    public function it_removes_project_id_from_task_if_flag_not_given()
    {
        /* Arrange */
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
        ]);

        /* Act */
        $response = $this->json('DELETE', route('projects.destroy', $this->project->external_id));

        /* Assert */
        $response->assertStatus(200);
        $this->assertNull($this->task->refresh()->deleted_at);
        $this->assertNull($this->task->refresh()->project_id);
        $this->assertNull($task->refresh()->deleted_at);
        $this->assertNull($task->refresh()->project_id);
    }

    #[Test]
    public function it_can_delete_project_if_there_is_no_tasks()
    {
        /* Arrange */
        $project = Project::factory()->create();

        /* Act */
        $response = $this->json('DELETE', route('projects.destroy', $project->external_id));

        /* Assert */
        $response->assertStatus(200);
        $this->assertNotNull($project->refresh()->deleted_at);
    }
}
