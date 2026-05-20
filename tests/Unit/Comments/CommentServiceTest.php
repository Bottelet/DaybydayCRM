<?php

namespace Tests\Unit\Comments;

use App\Models\Comment;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\Comment\CommentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

class CommentServiceTest extends AbstractTestCase
{
    use RefreshDatabase;

    private CommentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CommentService();
    }

    #[Test]
    public function it_creates_comment_on_task()
    {
        /* Arrange */
        $task        = Task::factory()->create();
        $user        = User::factory()->create();
        $description = 'This is a test comment on a task';

        /* Act */
        $comment = $this->service->createComment('task', $task->external_id, $description, $user->id);

        /* Assert */
        $this->assertInstanceOf(Comment::class, $comment);
        $this->assertStringContainsString($description, strip_tags($comment->description));
        $this->assertEquals($user->id, $comment->user_id);
        $this->assertEquals($task->id, $comment->commentable_id);
        $this->assertNotNull(Comment::where('user_id', $user->id)->first());
    }

    #[Test]
    public function it_creates_comment_on_lead()
    {
        /* Arrange */
        $lead        = Lead::factory()->create();
        $user        = User::factory()->create();
        $description = 'This is a test comment on a lead';

        /* Act */
        $comment = $this->service->createComment('lead', $lead->external_id, $description, $user->id);

        /* Assert */
        $this->assertInstanceOf(Comment::class, $comment);
        $this->assertStringContainsString($description, strip_tags($comment->description));
        $this->assertEquals($user->id, $comment->user_id);
        $this->assertEquals($lead->id, $comment->commentable_id);
    }

    #[Test]
    public function it_creates_comment_on_project()
    {
        /* Arrange */
        $project     = Project::factory()->create();
        $user        = User::factory()->create();
        $description = 'This is a test comment on a project';

        /* Act */
        $comment = $this->service->createComment('project', $project->external_id, $description, $user->id);

        /* Assert */
        $this->assertInstanceOf(Comment::class, $comment);
        $this->assertStringContainsString($description, strip_tags($comment->description));
        $this->assertEquals($user->id, $comment->user_id);
        $this->assertEquals($project->id, $comment->commentable_id);
    }

    #[Test]
    public function it_sanitizes_html_in_description()
    {
        /* Arrange */
        $task        = Task::factory()->create();
        $user        = User::factory()->create();
        $description = '<script>alert("xss")</script>Test comment';

        /* Act */
        $comment = $this->service->createComment('task', $task->external_id, $description, $user->id);

        /* Assert */
        $this->assertStringNotContainsString('<script>', $comment->description);
        $this->assertStringContainsString('Test comment', $comment->description);
    }

    #[Test]
    public function it_throws_exception_for_invalid_type()
    {
        /* Arrange */
        $task = Task::factory()->create();
        $user = User::factory()->create();

        /* Act & Assert */
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported comment type: invalid');
        $this->service->createComment('invalid', $task->external_id, 'Comments', $user->id);
    }

    #[Test]
    public function it_throws_exception_for_nonexistent_model()
    {
        /* Arrange */
        $user = User::factory()->create();

        /* Act & Assert */
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Could not find task with external ID');
        $this->service->createComment('task', 'nonexistent-id', 'Comments', $user->id);
    }

    #[Test]
    public function it_gets_supported_types()
    {
        /* Arrange & Act */
        $types = $this->service->getSupportedTypes();

        /* Assert */
        $this->assertCount(3, $types);
        $this->assertContains('task', $types);
        $this->assertContains('lead', $types);
        $this->assertContains('project', $types);
    }

    #[Test]
    public function it_checks_type_is_supported()
    {
        /* Arrange, Act & Assert */
        $this->assertTrue($this->service->isTypeSupported('task'));
        $this->assertTrue($this->service->isTypeSupported('lead'));
        $this->assertTrue($this->service->isTypeSupported('project'));
        $this->assertFalse($this->service->isTypeSupported('invalid'));
    }

    #[Test]
    public function it_deletes_comment()
    {
        /* Arrange */
        $comment   = Comment::factory()->create();
        $commentId = $comment->id;

        /* Act */
        $result = $this->service->deleteComment($comment);

        /* Assert */
        $this->assertTrue($result);
        $this->assertSoftDeleted('comments', ['id' => $commentId]);
    }

    #[Test]
    public function it_updates_comment()
    {
        /* Arrange */
        $comment        = Comment::factory()->create(['description' => 'Original']);
        $newDescription = 'Updated description';

        /* Act */
        $result = $this->service->updateComment($comment, $newDescription);

        /* Assert */
        $this->assertTrue($result);
        $fresh = $comment->fresh();
        $this->assertStringContainsString($newDescription, strip_tags($fresh->description));
    }

    #[Test]
    public function it_updates_comment_with_sanitized_html()
    {
        /* Arrange */
        $comment        = Comment::factory()->create();
        $newDescription = '<img src=x onerror=alert("xss")>Safe text';

        /* Act */
        $result = $this->service->updateComment($comment, $newDescription);

        /* Assert */
        $this->assertTrue($result);
        $fresh = $comment->fresh();
        $this->assertStringNotContainsString('onerror', $fresh->description);
        $this->assertStringContainsString('Safe text', $fresh->description);
    }
}
