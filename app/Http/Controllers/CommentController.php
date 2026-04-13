<?php

namespace App\Http\Controllers;

use App\Http\Requests\Comment\StoreCommentRequest;
use Illuminate\Support\Facades\Session;
use InvalidArgumentException;

class CommentController extends Controller
{
    /**
     * Create a comment for tasks and leads
     *
     * @param  $id
     * @return mixed
     */
    public function store(StoreCommentRequest $request)
    {
        $modelsMapping = [
            'task' => 'App\Models\Task',
            'lead' => 'App\Models\Lead',
            'project' => 'App\Models\Project',
        ];

        if (! array_key_exists($request->validated('type'), $modelsMapping)) {
            \Illuminate\Support\Facades\Session::flash('flash_message_warning', __('Could not create comment, type not found! Please contact Daybyday support'));
            throw new InvalidArgumentException('Could not create comment with type '.$request->validated('type'));
        }

        $model = $modelsMapping[$request->validated('type')];
        $source = $model::whereExternalId($request->validated('external_id'))->first();

        if ($source === null) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Source not found'], 404);
            }
            return redirect()->back()->with('error', 'Source not found');
        $model = $modelsMapping[$request->validated('type')];
        $source = $model::whereExternalId($request->validated('external_id'))->first();
        if (! $source) {
            if ($request->expectsJson()) {
                return response()->json(['error' => __('Source not found')], 404);
            }
            Session::flash('flash_message_warning', __('Could not create comment, source not found'));
            return redirect()->back();
        }
        $source->comments()->create(
            [
                'description' => clean($request->validated('description')),
                'user_id' => auth()->user()->id,
            ]
        );

        Session::flash('flash_message', __('Comment successfully added')); // Snippet in Master.blade.php

        return redirect()->back();
    }
}