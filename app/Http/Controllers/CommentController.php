<?php
namespace App\Http\Controllers;

use Auth;
use Session;
use App\Http\Requests;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Create a comment for tasks
     * @param Request $commentRequest
     * @param $id
     * @return mixed
     */
    public function store(Request $commentRequest, $id)
    {
        $this->validate($commentRequest, [
            'description' => 'required',
            'task_id' => '',
            'user_id' => '']);

        $input = $commentRequest = array_merge(
            $commentRequest->all(),
            ['task_id' => $id, 'user_id' => Auth::id()]
        );
        Comment::create($input);
        Session::flash('flash_message', 'Comment successfully added!'); //Snippet in Master.blade.php
        return redirect()->back();
    }
}
