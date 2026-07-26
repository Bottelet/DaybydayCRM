<?php

namespace Tests\Feature\Controllers;

use App\Http\Controllers\CommentController;
use App\Models\Comment;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
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
        $response = $this->from(route('tasks.show', $task->external_id))
            ->post(route('comments.create', ['type' => 'task', 'external_id' => $task->external_id]), [
                'description' => 'Test comment on task',
            ]);

        /* Assert */
        $response->assertRedirect(route('tasks.show', $task->external_id));
        $this->assertDatabaseHas('comments', [
            'user_id'     => $this->user->id,
            'source_type' => Task::class,
            'source_id'   => $task->id,
            'description' => '<p>Test comment on task</p>',
        ]);
    }

    #[Test]
    public function it_stores_comment_on_lead(): void
    {
        /* Arrange */
        $lead = Lead::factory()->create(['user_assigned_id' => $this->user->id]);

        /* Act */
        $response = $this->from(route('leads.show', $lead->external_id))
            ->post(route('comments.create', ['type' => 'lead', 'external_id' => $lead->external_id]), [
                'description' => 'Test comment on lead',
            ]);

        /* Assert */
        $response->assertRedirect(route('leads.show', $lead->external_id));
        $this->assertDatabaseHas('comments', [
            'user_id'     => $this->user->id,
            'source_type' => Lead::class,
            'source_id'   => $lead->id,
            'description' => '<p>Test comment on lead</p>',
        ]);
    }

    #[Test]
    public function it_stores_comment_on_project(): void
    {
        /* Arrange */
        $project = Project::factory()->create(['user_assigned_id' => $this->user->id]);

        /* Act */
        $response = $this->from(route('projects.show', $project->external_id))
            ->post(route('comments.create', ['type' => 'project', 'external_id' => $project->external_id]), [
                'description' => 'Test comment on project',
            ]);

        /* Assert */
        $response->assertRedirect(route('projects.show', $project->external_id));
        $this->assertDatabaseHas('comments', [
            'user_id'     => $this->user->id,
            'source_type' => Project::class,
            'source_id'   => $project->id,
            'description' => '<p>Test comment on project</p>',
        ]);
    }

    #[Test]
    public function it_rejects_comment_with_invalid_type(): void
    {
        /* Arrange */
        $task = Task::factory()->create(['user_assigned_id' => $this->user->id]);

        /* Act */
        $response = $this->from(route('tasks.show', $task->external_id))
            ->post(route('comments.create', ['type' => 'invoice', 'external_id' => $task->external_id]), [
                'description' => 'Should fail',
            ]);

        /* Assert */
        $response->assertRedirect(route('tasks.show', $task->external_id));
        $response->assertSessionHasErrors('type');
        $this->assertDatabaseMissing('comments', ['description' => 'Should fail']);
    }

    #[Test]
    public function it_rejects_comment_with_nonexistent_external_id(): void
    {
        /* Arrange */

        /* Act */
        $response = $this->from(route('tasks.index'))
            ->post(route('comments.create', ['type' => 'task', 'external_id' => 'nonexistent-uuid']), [
                'description' => 'Should fail',
            ]);

        /* Assert */
        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHasErrors('external_id');
        $this->assertDatabaseMissing('comments', ['description' => 'Should fail']);
    }

    #[Test]
    public function it_rejects_comment_with_empty_description(): void
    {
        /* Arrange */
        $task = Task::factory()->create(['user_assigned_id' => $this->user->id]);

        /* Act */
        $response = $this->from(route('tasks.show', $task->external_id))
            ->post(route('comments.create', ['type' => 'task', 'external_id' => $task->external_id]), [
                'description' => '',
            ]);

        /* Assert */
        $response->assertRedirect(route('tasks.show', $task->external_id));
        $response->assertSessionHasErrors('description');
        $this->assertDatabaseMissing('comments', ['user_id' => $this->user->id]);
    }

    #[Test]
    public function it_allows_any_authenticated_user_to_comment_on_a_task_they_are_not_assigned_to(): void
    {
        /* Arrange: StoreCommentRequest::authorize() only checks auth()->check() -
         * there is no per-resource ownership/assignment check, matching this
         * app's permission-based (not resource-ownership-based) authorization
         * model elsewhere, and no COMMENT_* permission exists to gate this
         * with. This test documents that current, intentional behavior. */
        $otherUser               = User::factory()->create();
        $taskAssignedToOtherUser = Task::factory()->create(['user_assigned_id' => $otherUser->id]);

        /* Act */
        $response = $this->from(route('tasks.show', $taskAssignedToOtherUser->external_id))
            ->post(route('comments.create', [
                'type' => 'task', 'external_id' => $taskAssignedToOtherUser->external_id,
            ]), [
                'description' => 'Commenting on a task assigned to someone else',
            ]);

        /* Assert */
        $response->assertRedirect(route('tasks.show', $taskAssignedToOtherUser->external_id));
        // CommentService sanitizes via clean(), which wraps plain text in <p> tags.
        $comment = Comment::query()->where('user_id', $this->user->id)->latest('id')->first();
        $this->assertNotNull($comment);
        $this->assertStringContainsString('Commenting on a task assigned to someone else', $comment->description);
    }

    #[Test]
    public function it_rejects_comment_from_unauthenticated_user(): void
    {
        /* Arrange: bypass route-level 'auth' middleware (which would otherwise
         * redirect to login before the request reaches the controller) so this
         * exercises StoreCommentRequest::authorize()'s own check directly. */
        $task = Task::factory()->create(['user_assigned_id' => $this->user->id]);
        auth()->logout();

        /* Act */
        $response = $this->withoutMiddleware()->postJson(route('comments.create', [
            'type' => 'task', 'external_id' => $task->external_id,
        ]), [
            'description' => 'Should be blocked',
        ]);

        /* Assert */
        $response->assertForbidden();
        $this->assertDatabaseMissing('comments', ['description' => 'Should be blocked']);
    }
}
