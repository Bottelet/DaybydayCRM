<?php
namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Client;
use Illuminate\Http\Request;
use Session;
use App\User;
use App\Tasks;
use Auth;
use Datatables;
use Pusher;
use Config;
use Dinero;
use App\Settings;
use DB;
use App\Industry;

class ClientsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $clients = Client::all();
        return view('clients.index')->withClients($clients);
    }

    public function anyData()
    {
        $clients = Client::select(['id', 'name', 'company_name', 'email', 'primary_number']);
        return Datatables::of($clients)
        ->addColumn('namelink', function ($clients) {
                return '<a href="clients/'.$clients->id.'" ">'.$clients->name.'</a>';
        })
        ->addColumn('action', function ($clients) {
                return '<a href="clients/'.$clients->id.'/edit" class="btn btn-success"> Edit</a>';
        })->make(true);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {

        $canCreateClient = Auth::user()->canDo('client.create');
        if (!$canCreateClient) {
            Session::flash('flash_message', 'Not allowed to create client!');
            return redirect()->route('users.index');
        }
        $industries = Industry::lists('name', 'id');
        $users =  User::select(array('users.name', 'users.id',
        DB::raw('CONCAT(users.name, " (", departments.name, ")") AS full_name')))
        ->join('department_user', 'users.id', '=', 'department_user.user_id')
        ->join('departments', 'department_user.department_id', '=', 'departments.id')
        ->lists('full_name', 'id');
        return view('clients.create')->withUsers($users)->withIndustries($industries);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $clientRequest)
    {
            $this->validate($clientRequest, [
            'name' => 'required',
            'company_name' => 'required',
            'vat' => 'max:8',
            'email' => 'required',
            'address' => '',
            'zipcode' => 'max:4',
            'city' => '',
            'primary_number' => 'max:10',
            'secondary_number' => 'max:10',
            'industry' => '',
            'company_type' => '',
            'fk_user_id' => 'required'

            ]);

            $input = $clientRequest->all();
            Client::create($input);
        //dd($input);
            Session::flash('flash_message', 'Client successfully added!');
            return redirect()->route('clients.index');
    }

    public function cvrapistart(Request $vatRequest)
    {
        $this->validate($vatRequest, [
            'vat' => 'required',
            'name' => '',
            'company_name' => '',
            'vat' => '',
            'email' => '',
            'address' => '',
            'zipcode' => '',
            'city' => '',
            'primary_number' => '',
            'secondary_number' => '',
            'industry' => '',
            'company_type' => '',
            'fk_user_id' => ''

            ]);
        
        $vat = $vatRequest->input('vat');

        $country = $vatRequest->input('country');
        $company_name = $vatRequest->input('company_name');

        // Strip all other characters than numbers
        $vat = preg_replace('/[^0-9]/', '', $vat);
        
        function cvrapi($vat)
        {
       
            if (empty($vat)) {
            // Print error message
                return('Please insert VAT');
            } else {
                // Start cURL
                $ch = curl_init();

                // Set cURL options
                curl_setopt($ch, CURLOPT_URL, 'http://cvrapi.dk/api?search=' . $vat . '&country=dk');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_USERAGENT, 'Flashpoint');

                // Parse result
                $result = curl_exec($ch);

                // Close connection when done
                curl_close($ch);

                // Return our decoded result
                return json_decode($result, 1);
            }
        }
        $result = cvrapi($vat, 'dk');

        return redirect()->back()->with('data', $result);
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
          $client = Client::with('Tasks', 'Tasks.assignee')->findOrFail($id);
          $settings = Settings::findOrFail(1);
          $companyname = $settings->company;
          return view('clients.show')->with(compact('client', 'companyname'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $canUpdateClient = Auth::user()->canDo('client.update');
        if (!$canUpdateClient) {
            Session::flash('flash_message', 'Not allowed to update client!');
            return redirect()->route('users.index');
        }
        $users = User::lists('name', 'id');
        $client = Client::findorFail($id);
        return view('clients.edit')->withClient($client)->withUsers($users);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id, Request $request)
    {
            $client = Client::findOrFail($id);

            $this->validate($request, [
            'name' => 'required',
            'company_name' => 'required',
            'vat' => 'max:8',
            'email' => 'required',
            'address' => '',
            'zipcode' => 'max:4',
            'city' => '',
            'primary_number' => 'max:10',
            'secondary_number' => 'max:10',
            'industry' => '',
            'company_type' => '',
            'fk_user_id' => 'required'

            ]);

            $input = $request->all();
            $client->fill($input)->save();

            Session::flash('flash_message', 'Client successfully updated!');
            return redirect()->route('clients.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $client = Client::findorFail($id);
        $client->delete();
        return redirect()->route('clients.index');
    }
}
