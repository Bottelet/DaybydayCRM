<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Integration;

class IntegrationsController extends Controller
{
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();

        $existing = Integration::where([
           // 'user_id' => $request->post['user_id'] ? $userId : null,
            'api_type'  => $request->api_type
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
