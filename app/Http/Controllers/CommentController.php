<?php

namespace App\Http\Controllers;

use App\Http\Requests\Comment\StoreCommentRequest;
use App\Services\Comment\CommentService;
use Illuminate\Support\Facades\Session;
use InvalidArgumentException;

class CommentController extends Controller
{
    public function __construct(private CommentService $commentService) {}

    /**
     * Create a comment for tasks, leads, and projects.
     *
     * @return mixed
     */
    public function store(StoreCommentRequest $request)
    {
        try {
            $this->commentService->createComment(
                $request->validated('type'),
                $request->validated('external_id'),
                $request->validated('description'),
                auth()->user()->id
            );
        } catch (InvalidArgumentException $exception) {
            // StoreCommentRequest already validates type/existence, so this
            // path is normally unreachable - kept as defensive parity with
            // the controller's previous behavior in case that ever changes.
            $message = __('Could not create comment, type not found! Please contact Daybyday support');
            if ($request->expectsJson()) {
                return response()->json(['error' => $message], 400);
            }
            Session::flash('flash_message_warning', $message);

            return redirect()->back();
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => __('Comment successfully added')], 201);
        }

        Session::flash('flash_message', __('Comment successfully added'));

        return redirect()->back();
    }
}
