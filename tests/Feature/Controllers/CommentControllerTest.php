<?php

namespace Tests\Feature\Controllers;

use App\Http\Controllers\CommentController;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

#[CoversClass(CommentController::class)]
class CommentControllerTest extends AbstractTestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_stores_comment_on_task(): void
    {
        $task = Task::factory()->create(['user_assigned_id' => $this->user->id]);

        $response = $this->post(route('comments.create', ['type' => 'task', 'external_id' => $task->external_id]), [
            'description' => 'Test comment on task',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('comments', ['user_id' => $this->user->id]);
    }

    #[Test]
    public function it_stores_comment_on_lead(): void
    {
        $lead = Lead::factory()->create(['user_assigned_id' => $this->user->id]);

        $response = $this->post(route('comments.create', ['type' => 'lead', 'external_id' => $lead->external_id]), [
            'description' => 'Test comment on lead',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('comments', ['user_id' => $this->user->id]);
    }

    #[Test]
    public function it_stores_comment_on_project(): void
    {
        $project = Project::factory()->create(['user_assigned_id' => $this->user->id]);

        $response = $this->post(route('comments.create', ['type' => 'project', 'external_id' => $project->external_id]), [
            'description' => 'Test comment on project',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('comments', ['user_id' => $this->user->id]);
    }

    #[Test]
    public function it_rejects_comment_with_invalid_type(): void
    {
        $task = Task::factory()->create(['user_assigned_id' => $this->user->id]);

        $response = $this->post(route('comments.create', ['type' => 'invoice', 'external_id' => $task->external_id]), [
            'description' => 'Should fail',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('comments', ['description' => 'Should fail']);
    }

    #[Test]
    public function it_rejects_comment_with_nonexistent_external_id(): void
    {
        $response = $this->post(route('comments.create', ['type' => 'task', 'external_id' => 'nonexistent-uuid']), [
            'description' => 'Should fail',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('comments', ['description' => 'Should fail']);
    }

    #[Test]
    public function it_rejects_comment_with_empty_description(): void
    {
        $task = Task::factory()->create(['user_assigned_id' => $this->user->id]);

        $response = $this->post(route('comments.create', ['type' => 'task', 'external_id' => $task->external_id]), [
            'description' => '',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('comments', ['user_id' => $this->user->id]);
    }
}
