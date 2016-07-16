<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Leads;
use App\User;
use App\Client;
use App\Http\Requests;
use Session;
use App\Http\Controllers\Controller;
use App\Settings;
use Auth;
use Datatables;
use Carbon;
use App\Comment;
use DB;
use App\Http\Requests\Lead\StoreLeadRequest;
use App\Http\Requests\Lead\UpdateLeadFollowUpRequest;

class LeadsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $leads = Leads::all()->where('status', 1);
        return view('leads.index')->withLeads($leads);
    }
    
    public function anyData()
    {

        $leads = Leads::select(
            ['id', 'title', 'fk_user_id_created', 'fk_client_id', 'fk_user_id_assign', 'contact_date']
        )->where('status', 1)->get();
        return Datatables::of($leads)
        ->addColumn('titlelink', function ($leads) {
                return '<a href="leads/'.$leads->id.'" ">'.$leads->title.'</a>';
        })
        ->editColumn('fk_user_id_created', function ($leads) {
                return $leads->createdBy->name;
        })
        ->editColumn('contact_date', function ($leads) {
                return $leads->contact_date ? with(new Carbon($leads->created_at))
                ->format('d/m/Y') : '';
        })
        ->editColumn('fk_user_id_assign', function ($leads) {
                return $leads->assignee->name;
        })->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $canCreateLead = Auth::user()->canDo('lead.create');
        if (!$canCreateLead) {
            Session::flash('flash_message', 'Not allowed to create lead!');
            return redirect()->route('users.index');
        }
        $users = User::select(
            array('users.name', 'users.id',
                DB::raw('CONCAT(users.name, " (", departments.name, ")") AS full_name'))
        )
        ->join('department_user', 'users.id', '=', 'department_user.user_id')
        ->join('departments', 'department_user.department_id', '=', 'departments.id')
        ->lists('full_name', 'id');
        $clients = Client::lists('name', 'id');
        ;
        return view('leads.create')->withUsers($users)->withClients($clients);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreLeadRequest $request)
    {
          $fk_client_id = $request->get('fk_client_id');
          $input = $request = array_merge(
              $request->all(),
              ['fk_user_id_created' => \Auth::id(),
               'contact_date' => $request->contact_date ." " . $request->contact_time . ":00"]
          );
       
          $lead = Leads::create($input);
          Session::flash('flash_message', 'Lead successfully added!'); //Snippet in Master.blade.php
          return redirect()->to("/leads/{$lead->id}");
    }
   
    public function updateassign($id, Request $request)
    {

        $lead = Leads::findOrFail($id);
        $settings = Settings::all();

        $settingscomplete = $settings[0]['lead_assign_allowed'];

        if ($settingscomplete == 1  && Auth::user()->id == $lead->fk_user_id_assign || $isAdmin) {
            Session::flash('flash_message_warning', 'Only assigned user are allowed to assign new user.');
                return redirect()->back();
        }
             $input = $request->get('fk_user_id_assign');
                $input = array_replace($request->all());
                $lead->fill($input)->save();
                return redirect()->back();
    }

    public function updatefollowup(UpdateLeadFollowUpRequest $request, $id)
    {
         $lead = Leads::findOrFail($id);
         $input = $request->all();
         $input = $request =
         [ 'contact_date' => $request->contact_date ." " . $request->contact_time . ":00"];
         $lead->fill($input)->save();
         return redirect()->back();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
         $settings = Settings::findOrFail(1);
        $companyname = $settings->company;

        $users =  User::select(array(
            'users.name', 'users.id',
            DB::raw('CONCAT(users.name, " (", departments.name, ")") AS full_name')))
        ->join('department_user', 'users.id', '=', 'department_user.user_id')
        ->join('departments', 'department_user.department_id', '=', 'departments.id')
        ->lists('full_name', 'id');
        $leads = Leads::findorFail($id);
        return view('leads.show')->withLeads($leads)->withUsers($users)->withCompanyname($companyname);
    }

    public function updatestatus($id, Request $request)
    {

        $lead = Leads::findOrFail($id);
        $isAdmin = Auth::user()->hasRole('admin');

        $settings = Settings::all();
        $settingscomplete = $settings[0]['lead_complete_allowed'];
        if ($settingscomplete == 1  && Auth::user()->id == $lead->fk_user_id_assign || $isAdmin) {
            Session::flash('flash_message_warning', 'Only assigned user are allowed to close lead.');
            return redirect()->back();
        }

        $input = $request->get('status');
        $input = array_replace($request->all(), ['status' => 2]);
        $lead->fill($input)->save();
        $commentInput = array_merge(
            ['fk_lead_id' => $id, 'fk_user_id' => \Auth::id(),
             'description' => Auth::user()->name.' Completed the lead']
        );
        Comment::create($commentInput);
        return redirect()->back();
    }
}
