<?php
namespace App\Http\Controllers;

use App\Http\Requests;
use App\Models\Integration;
use Illuminate\Http\Request;

class IntegrationsController extends Controller
{
    public function __construct()
    {
        $this->middleware('user.is.admin', ['only' => ['index']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $check = Integration::all();
        return view('integrations.index')->withCheck($check);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();

        $existing = Integration::where([
            // 'user_id' => $request->post['user_id'] ? $userId : null,
            'api_type' => $request->api_type
        ])->get();
        $existing = isset($existing[0]) ? $existing[0] : null;

        if ($existing) {
            $existing->fill($input)->save();
        } else {
            Integration::create($input);
        }

        return $this->index();
    }
}
