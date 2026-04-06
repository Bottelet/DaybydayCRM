<?php

namespace Tests\Unit\Events;

use App\Events\NewComment;
use App\Models\Comment;
use Illuminate\Foundation\Testing\DatabaseTransactions;
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
        $comment = factory(Comment::class)->create();
        $this->expectsEvents(NewComment::class);

        NewComment::dispatch($comment);
    }
}