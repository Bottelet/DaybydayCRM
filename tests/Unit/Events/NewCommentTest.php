<?php

namespace Tests\Unit\Events;

use App\Events\NewComment;
use App\Models\Comment;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class NewCommentTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function constructor_stores_comment()
    {
        $comment = factory(Comment::class)->create();

        $event = new NewComment($comment);

        $this->assertEquals($comment->id, $event->comment->id);
    }

    /** @test */
    public function comment_property_is_public()
    {
        $comment = factory(Comment::class)->create();
        $event = new NewComment($comment);

        $this->assertInstanceOf(Comment::class, $event->comment);
    }

    /** @test */
    public function event_preserves_comment_description()
    {
        $comment = factory(Comment::class)->create(['description' => 'Test comment text']);
        $event = new NewComment($comment);

        $this->assertEquals('Test comment text', $event->comment->description);
    }

    /** @test */
    public function event_can_be_dispatched()
    {
        Event::fake();
        $comment = factory(Comment::class)->create();

        NewComment::dispatch($comment);

        Event::assertDispatched(NewComment::class);
    }
}
