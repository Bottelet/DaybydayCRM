<?php

namespace App\Http\Controllers;

use App\Http\Requests\Comment\StoreCommentRequest;
use Illuminate\Support\Facades\Session;

class CommentController extends Controller
{
    /**
     * Create a comment for tasks and leads.
     *
     * @param $id
     *
     * @return mixed
     */
    public function store(StoreCommentRequest $request)
    {
        $modelsMapping = [
            'task'    => 'App\\Models\\Task',
            'lead'    => 'App\\Models\\Lead',
            'project' => 'App\\Models\\Project',
        ];

        if ( ! array_key_exists($request->validated('type'), $modelsMapping)) {
            $message = __('Could not create comment, type not found! Please contact Daybyday support');
            if ($request->expectsJson()) {
                return response()->json(['error' => $message], 400);
            }
            Session::flash('flash_message_warning', $message);

            return redirect()->back();
        }

        $model  = $modelsMapping[$request->validated('type')];
        $source = $model::findByExternalId($request->validated('external_id'));

        // At this point, $source is guaranteed to exist due to FormRequest validation
        $source->comments()->create([
            'description' => clean($request->validated('description')),
            'user_id'     => auth()->user()->id,
        ]);

        Session::flash('flash_message', __('Comment successfully added'));

        return redirect()->back();
    }
}
