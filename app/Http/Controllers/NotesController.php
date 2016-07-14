<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Auth;
use Session;
use App\Leads;
use App\Note;

class NotesController extends Controller
{
    public function store(Request $request, $id)
    {
        $this->validate($request, [
                'note' => 'required',
                'status' => '',
                'fk_lead_id' => '',
                'fk_user_id' => '']);

        $input = $request = array_merge(
            $request->all(),
            ['fk_lead_id' => $id, 'fk_user_id' => \Auth::id(), 'status' => 0]
        );

        Note::create($input);
        Session::flash('flash_message', 'Note successfully added!'); //Snippet in Master.blade.php
        return redirect()->back();
    }
}
