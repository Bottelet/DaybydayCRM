<?php

namespace App\Listeners;

use App\Events\NewComment;
use App\Models\User;
use App\Notifications\YouWereMentionedNotification;

class NotiftyMentionedUsers
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct() {}

    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(NewComment $event)
    {
        collect($event->comment->mentionedUsers())
            ->map(function ($name) {
                return User::where('name', $name)->first();
            })
            ->filter()
            ->each(function ($user) use ($event) {
                $user->notify(new YouWereMentionedNotification($event->comment));
            });
    }
}
