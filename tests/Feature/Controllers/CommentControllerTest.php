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
        /* Arrange */
        $task = Task::factory()->create(['user_assigned_id' => $this->user->id]);

        /* Act */
        $response = $this->post(route('comments.create', ['type' => 'task', 'external_id' => $task->external_id]), [
            'description' => 'Test comment on task',
        ]);

        /* Assert */
        $response->assertRedirect();
        $this->assertDatabaseHas('comments', ['user_id' => $this->user->id]);
    }

    #[Test]
    public function it_stores_comment_on_lead(): void
    {
        /* Arrange */
        $lead = Lead::factory()->create(['user_assigned_id' => $this->user->id]);

        /* Act */
        $response = $this->post(route('comments.create', ['type' => 'lead', 'external_id' => $lead->external_id]), [
            'description' => 'Test comment on lead',
        ]);

        /* Assert */
        $response->assertRedirect();
        $this->assertDatabaseHas('comments', ['user_id' => $this->user->id]);
    }

    #[Test]
    public function it_stores_comment_on_project(): void
    {
        /* Arrange */
        $project = Project::factory()->create(['user_assigned_id' => $this->user->id]);

        /* Act */
        $response = $this->post(route('comments.create', ['type' => 'project', 'external_id' => $project->external_id]), [
            'description' => 'Test comment on project',
        ]);

        /* Assert */
        $response->assertRedirect();
        $this->assertDatabaseHas('comments', ['user_id' => $this->user->id]);
    }

    #[Test]
    public function it_rejects_comment_with_invalid_type(): void
    {
        /* Arrange */
        $task = Task::factory()->create(['user_assigned_id' => $this->user->id]);

        /* Act */
        $response = $this->post(route('comments.create', ['type' => 'invoice', 'external_id' => $task->external_id]), [
            'description' => 'Should fail',
        ]);

        /* Assert */
        $response->assertRedirect();
        $this->assertDatabaseMissing('comments', ['description' => 'Should fail']);
    }

    #[Test]
    public function it_rejects_comment_with_nonexistent_external_id(): void
    {
        /* Arrange */

        /* Act */
        $response = $this->post(route('comments.create', ['type' => 'task', 'external_id' => 'nonexistent-uuid']), [
            'description' => 'Should fail',
        ]);

        /* Assert */
        $response->assertRedirect();
        $this->assertDatabaseMissing('comments', ['description' => 'Should fail']);
    }

    #[Test]
    public function it_rejects_comment_with_empty_description(): void
    {
        /* Arrange */
        $task = Task::factory()->create(['user_assigned_id' => $this->user->id]);

        /* Act */
        $response = $this->post(route('comments.create', ['type' => 'task', 'external_id' => $task->external_id]), [
            'description' => '',
        ]);

        /* Assert */
        $response->assertRedirect();
        $this->assertDatabaseMissing('comments', ['user_id' => $this->user->id]);
    }
}
