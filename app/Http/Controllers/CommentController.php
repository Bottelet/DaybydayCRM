<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Comment;
use Illuminate\Http\Request;
use Auth;
use Session;
use App\Tasks;

class CommentController extends Controller {

	
	public function store(Request $commentRequest, $id)
	{
		$this->validate($commentRequest, [
				'description' => 'required',
				'fk_task_id' => '',
				'fk_user_id' => '']);

		$input = $commentRequest = array_merge($commentRequest->all(),
		['fk_task_id' => $id, 'fk_user_id' => \Auth::id()]);
		//dd($input);
		Comment::create($input);
		Session::flash('flash_message', 'Comment successfully added!'); //Snippet in Master.blade.php
        return redirect()->back();
	}

}
