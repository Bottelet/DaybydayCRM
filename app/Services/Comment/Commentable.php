<?php
namespace App\Services\Comment;

use Illuminate\Database\Eloquent\Relations\MorphMany;

interface Commentable
{
    public function comments(): MorphMany;
    public function getCreateCommentEndpoint(): String;
}
