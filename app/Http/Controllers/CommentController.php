<?php
namespace App\Http\Controllers;

use Auth;
use Session;
use App\Models\Tasks;
use App\Http\Requests;
use App\Models\Comment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CommentController extends Controller
{
    public function store(Request $commentRequest, $id)
    {
        $this->validate($commentRequest, [
                'description' => 'required',
                'fk_task_id' => '',
                'fk_user_id' => '']);

        $input = $commentRequest = array_merge(
            $commentRequest->all(),
            ['fk_task_id' => $id, 'fk_user_id' => Auth::id()]
        );
        Comment::create($input);
        Session::flash('flash_message', 'Comment successfully added!'); //Snippet in Master.blade.php
        return redirect()->back();
    }
}
