<?php
namespace Tests\Unit\Controllers\Project;

use Tests\TestCase;
use App\Models\Project;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Task;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DeleteProjectControllerTest extends TestCase
{
    use DatabaseTransactions;

    private $project;
    private $task;

    public function setUp(): void
    {
        parent::setUp();

        $this->project = factory(Project::class)->create();
        $this->task = factory(Task::class)->create([
            'project_id' => $this->project->id,
        ]);
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    /** @test */
    public function deleteProject()
    {
        $this->json('DELETE', route('projects.destroy', $this->project->external_id));
        
        $this->assertSoftDeleted('projects', ['id' => $this->project->id]);
    }

    /** @test */
    public function deleteTasksIfFlagGiven()
    {   
        $task = factory(Task::class)->create([
            'project_id' => $this->project->id,
        ]);

        $this->json('DELETE', route('projects.destroy', $this->project->external_id), [
            'delete_tasks' => "on"
        ]);
        
        $this->assertSoftDeleted('projects', ['id' => $this->project->id]);
        $this->assertSoftDeleted('tasks', ['id' => $this->task->id]);
        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }

    /** @test */
    public function removeProjectIdFromTaskIfFlagNotGiven()
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
    public function canDeleteProjectIfThereIsNoTasks()
    {   
        $project = factory(Project::class)->create();
        $this->json('DELETE', route('projects.destroy', $project->external_id));

        $this->assertnotNull($project->refresh()->deleted_at);
    }
}
