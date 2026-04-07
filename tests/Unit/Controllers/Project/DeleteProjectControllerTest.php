<?php

namespace Tests\Unit\Controllers\Project;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DeleteProjectControllerTest extends TestCase
{
    use DatabaseTransactions;

    private $project;

    private $task;

    protected function setUp(): void
    {
        parent::setUp();

        $this->project = factory(Project::class)->create();
        $this->task = factory(Task::class)->create([
            'project_id' => $this->project->id,
        ]);
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    /** @test */
    public function delete_project()
    {
        $this->json('DELETE', route('projects.destroy', $this->project->external_id));

        $this->assertSoftDeleted('projects', ['id' => $this->project->id]);
    }

    /** @test */
    public function delete_tasks_if_flag_given()
    {
        $task = factory(Task::class)->create([
            'project_id' => $this->project->id,
        ]);

        $this->json('DELETE', route('projects.destroy', $this->project->external_id), [
            'delete_tasks' => 'on',
        ]);

        $this->assertSoftDeleted('projects', ['id' => $this->project->id]);
        $this->assertSoftDeleted('tasks', ['id' => $this->task->id]);
        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }

    /** @test */
    public function remove_project_id_from_task_if_flag_not_given()
    {
        $task = factory(Task::class)->create([
            'project_id' => $this->project->id,
        ]);

        $this->json('DELETE', route('projects.destroy', $this->project->external_id));

        $this->assertNull($this->task->refresh()->deleted_at);
        $this->assertNull($this->task->refresh()->project_id);

        $this->assertNull($task->refresh()->deleted_at);
        $this->assertNull($task->refresh()->project_id);
    }

    /** @test */
    public function can_delete_project_if_there_is_no_tasks()
    {
        $project = factory(Project::class)->create();
        $this->json('DELETE', route('projects.destroy', $project->external_id));

        $this->assertnotNull($project->refresh()->deleted_at);
    }
}
