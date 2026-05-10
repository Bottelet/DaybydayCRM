<?php

namespace Tests\Unit\Events;

use App\Events\NewComment;
use App\Models\Comment;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

class NewCommentTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Freeze time for deterministic tests
        Carbon::setTestNow('2024-01-15 12:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    # region happy_path

    #[Test]
    public function it_constructor_stores_comment()
    {
        /** Arrange */
        $comment = Comment::factory()->create();

        /** Act */
        $event = new NewComment($comment);

        /* Assert */
        $this->assertEquals($comment->id, $event->comment->id);
    }

    #[Test]
    public function it_comment_property_is_public()
    {
        /** Arrange */
        $comment = Comment::factory()->create();

        /** Act */
        $event = new NewComment($comment);

        /* Assert */
        $this->assertInstanceOf(Comment::class, $event->comment);
    }

    #[Test]
    public function it_event_preserves_comment_description()
    {
        /** Arrange */
        $comment = Comment::factory()->create(['description' => 'Test comment text']);

        /** Act */
        $event = new NewComment($comment);

        /* Assert */
        $this->assertEquals('Test comment text', $event->comment->description);
    }

    #[Test]
    public function it_event_can_be_dispatched()
    {
        /* Arrange */
        Event::fake();
        $comment = Comment::factory()->create();

        /* Act */
        NewComment::dispatch($comment);

        /* Assert */
        Event::assertDispatched(NewComment::class);
    }

    #[Test]
    public function it_dispatched_event_carries_correct_comment()
    {
        /* Arrange */
        Event::fake();
        $comment = Comment::factory()->create(['description' => 'dispatch check']);

        /* Act */
        NewComment::dispatch($comment);

        /* Assert */
        Event::assertDispatched(NewComment::class, function ($event) use ($comment) {
            return $event->comment->id === $comment->id;
        });
    }

    #[Test]
    public function it_event_uses_dispatchable_trait()
    {
        /** Arrange */
        // No arrangement needed

        /** Act */
        $traits = class_uses(NewComment::class);

        /* Assert */
        $this->assertContains('Illuminate\Foundation\Events\Dispatchable', $traits);
    }

    #[Test]
    public function it_event_uses_serializes_models_trait()
    {
        /** Arrange */
        // No arrangement needed

        /** Act */
        $traits = class_uses(NewComment::class);

        /* Assert */
        $this->assertContains('Illuminate\Queue\SerializesModels', $traits);
    }

    # endregion

    # region edge_cases

    #[Test]
    public function it_event_preserves_null_description()
    {
        /** Arrange */
        $comment = Comment::factory()->create(['description' => null]);

        /** Act */
        $event = new NewComment($comment);

        /* Assert */
        $this->assertNull($event->comment->description);
    }

    #[Test]
    public function it_event_preserves_empty_description()
    {
        /** Arrange */
        $comment = Comment::factory()->create(['description' => '']);

        /** Act */
        $event = new NewComment($comment);

        /* Assert */
        $this->assertEquals('', $event->comment->description);
    }

    #[Test]
    public function it_dispatched_event_preserves_comment_relationships()
    {
        /* Arrange */
        Event::fake();
        $comment = Comment::factory()->create();

        /* Act */
        NewComment::dispatch($comment);

        /* Assert */
        Event::assertDispatched(NewComment::class, function ($event) use ($comment) {
            return $event->comment->user_id === $comment->user_id;
        });
    }

    # endregion
}
