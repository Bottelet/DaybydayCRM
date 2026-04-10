<?php

namespace Tests\Unit\Events;

use App\Events\NewComment;
use App\Models\Comment;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NewCommentTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function constructor_stores_comment()
    {
        $comment = Comment::factory()->create();

        $event = new NewComment($comment);

        $this->assertEquals($comment->id, $event->comment->id);
    }

    #[Test]
    public function comment_property_is_public()
    {
        $comment = Comment::factory()->create();
        $event = new NewComment($comment);

        $this->assertInstanceOf(Comment::class, $event->comment);
    }

    #[Test]
    public function event_preserves_comment_description()
    {
        $comment = Comment::factory()->create(['description' => 'Test comment text']);
        $event = new NewComment($comment);

        $this->assertEquals('Test comment text', $event->comment->description);
    }

    #[Test]
    public function event_can_be_dispatched()
    {
        Event::fake();
        $comment = Comment::factory()->create();

        NewComment::dispatch($comment);

        Event::assertDispatched(NewComment::class);
    }

    #[Test]
    public function event_uses_dispatchable_trait()
    {
        $traits = class_uses(NewComment::class);
        $this->assertContains('Illuminate\Foundation\Events\Dispatchable', $traits);
    }

    #[Test]
    public function event_uses_serializes_models_trait()
    {
        $traits = class_uses(NewComment::class);
        $this->assertContains('Illuminate\Queue\SerializesModels', $traits);
    }

    #[Test]
    public function dispatched_event_carries_correct_comment()
    {
        Event::fake();
        $comment = Comment::factory()->create(['description' => 'dispatch check']);

        NewComment::dispatch($comment);

        Event::assertDispatched(NewComment::class, function ($event) use ($comment) {
            return $event->comment->id === $comment->id;
        });
    }
}
