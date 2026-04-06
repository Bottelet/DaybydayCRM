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
    public function constructorStoresComment()
    {
        $comment = factory(Comment::class)->create();

        $event = new NewComment($comment);

        $this->assertEquals($comment->id, $event->comment->id);
    }

    /** @test */
    public function commentPropertyIsPublic()
    {
        $comment = factory(Comment::class)->create();
        $event = new NewComment($comment);

        $this->assertInstanceOf(Comment::class, $event->comment);
    }

    /** @test */
    public function eventPreservesCommentDescription()
    {
        $comment = factory(Comment::class)->create(['description' => 'Test comment text']);
        $event = new NewComment($comment);

        $this->assertEquals('Test comment text', $event->comment->description);
    }

    /** @test */
    public function eventCanBeDispatched()
    {
        Event::fake();
        $comment = factory(Comment::class)->create();

        NewComment::dispatch($comment);

        Event::assertDispatched(NewComment::class);
    }
}