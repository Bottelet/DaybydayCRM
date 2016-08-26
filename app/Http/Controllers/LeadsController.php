<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Leads;
use App\Models\User;
use App\Models\Client;
use App\Http\Requests;
use Session;
use App\Http\Controllers\Controller;
use App\Models\Settings;
use Auth;
use Datatables;
use Carbon;
use App\Models\Activity;
use DB;
use App\Http\Requests\Lead\StoreLeadRequest;
use App\Http\Requests\Lead\UpdateLeadFollowUpRequest;
use App\Repositories\Lead\LeadRepositoryContract;
use App\Repositories\User\UserRepositoryContract;
use App\Repositories\Client\ClientRepositoryContract;
use App\Repositories\Setting\SettingRepositoryContract;

class LeadsController extends Controller
{
    protected $leads;
    protected $clients;
    protected $settings;
    protected $users;

    public function __construct(
        LeadRepositoryContract $leads,
        UserRepositoryContract $users,
        ClientRepositoryContract $clients,
        SettingRepositoryContract $settings
    ) {
        $this->users = $users;
        $this->settings = $settings;
        $this->clients = $clients;
        $this->leads = $leads;
        $this->middleware('lead.create', ['only' => ['create']]);
        $this->middleware('lead.assigned', ['only' => ['updateAssign']]);
        $this->middleware('lead.update.status', ['only' => ['updateStatus']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('leads.index');
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
        return view('leads.create')
        ->withUsers($this->users->getAllUsersWithDepartments())
        ->withClients($this->clients->listAllClients());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreLeadRequest $request)
    {
        $getInsertedId = $this->leads->create($request);
        Session()->flash('flash_message', 'Lead is created');
        return redirect()->route('leads.show', $getInsertedId);
    }
   
    public function updateAssign($id, Request $request)
    {
        $this->leads->updateAssign($id, $request);
        Session()->flash('flash_message', 'New user is assigned');
        return redirect()->back();
    }

    public function updateFollowup(UpdateLeadFollowUpRequest $request, $id)
    {
        $this->leads->updateFollowup($id, $request);
        Session()->flash('flash_message', 'New follow up date is set');
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
        return view('leads.show')
        ->withLeads($this->leads->find($id))
        ->withUsers($this->users->getAllUsersWithDepartments())
        ->withCompanyname($this->settings->getCompanyName());
    }

    public function updateStatus($id, Request $request)
    {
        $this->leads->updateStatus($id, $request);
        Session()->flash('flash_message', 'Lead is completed');
        return redirect()->back();
    }
}
