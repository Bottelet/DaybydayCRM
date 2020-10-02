<?php
namespace App\Http\Controllers;

use App\Models\Invoice;
use DB;
use Auth;
use Carbon;
use Session;
use Datatables;
use App\Models\Lead;
use App\Models\User;
use App\Models\Client;
use App\Http\Requests;
use App\Models\Status;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Requests\Lead\StoreLeadRequest;
use App\Http\Requests\Lead\UpdateLeadFollowUpRequest;
use Ramsey\Uuid\Uuid;

class LeadsController extends Controller
{
    const CREATED = 'created';
    const UPDATED_STATUS = 'updated_status';
    const UPDATED_DEADLINE = 'updated_deadline';
    const UPDATED_ASSIGN = 'updated_assign';

    public function __construct()
    {
        $this->middleware('lead.create', ['only' => ['create']]);
        $this->middleware('lead.assigned', ['only' => ['updateAssign']]);
        $this->middleware('lead.update.status', ['only' => ['updateStatus']]);
    }


    public function index()
    {
        return view('leads.index')
        ->withStatuses(Status::typeOfLead()->get());;
    }
    
    public function allLeads()
    {
        $leads = Lead::with(['user', 'status'])->select('leads.*')->get();

        return Datatables::of($leads)
            ->addColumn('titlelink', function ($leads) {
                return '<a href="'.route('leads.show', $leads->external_id).'">'.$leads->title.'</a>';
            })
            ->editColumn('qualified', function ($leads) {
                return $leads->qualified ? __('True') : __('False');
            })
            ->editColumn('contact_date', function ($leads) {
                return $leads->deadline ? with(new Carbon($leads->deadline))
                    ->format(carbonDate()) : '';
            })
            ->editColumn('user_assigned_id', function ($leads) {
                return $leads->user->name;
            })
            ->editColumn('status_id', function ($leads) {
                return '<span class="label label-success" style="background-color:' . $leads->status->color . '"> ' .
                    $leads->status->title . '</span>';
            })
            ->addColumn('view', function ($leads) {
                return '<a href="' . route("leads.show", $leads->external_id) . '" class="btn btn-link">' . __('View') .'</a>'
                . '<a data-toggle="modal" data-id="'. route('leads.destroy',$leads->external_id) . '" data-title="'. $leads->title . '" data-target="#deletion" class="btn btn-link">' . __('Delete') .'</a>';
            })
            ->rawColumns(['titlelink','view', 'status_id'])
            ->make(true);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function unqualified()
    {
        return view('leads.unqualified')->withStatuses(Status::typeOfLead()->get());
    }

    /**
     * Data for Data tables
     * @return mixed
     */
    public function unqualifiedLeads()
    {
        $status_id = Status::typeOfLead()->where('title', 'Closed')->first()->id;
        $leads = Lead::isNotQualified()
            ->where('status_id', '!=', $status_id)
            ->with(['user', 'creator', 'client.primaryContact'])->get();

        $leads->map(function ($item) {
            return [$item['visible_deadline_date'] = $item['deadline']->format(carbonDate()), $item["visible_deadline_time"] = $item['deadline']->format(carbonTime())];
        });
        return $leads->toJson();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($client_external_id = null)
    {
        $client =  Client::whereExternalId($client_external_id);

        return view('leads.create')
            ->withUsers(User::with(['department'])->get()->pluck('nameAndDepartmentEagerLoading', 'id'))
            ->withClients(Client::pluck('company_name', 'external_id'))
            ->withClient($client ?: null)
            ->withStatuses(Status::typeOfLead()->pluck('title', 'id'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreLeadRequest|Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreLeadRequest $request)
    {
        if ($request->client_external_id) {
            $client = Client::whereExternalId($request->client_external_id);
        }

        $lead = Lead::create(
            [
                'title' => $request->title,
                'description' => clean($request->description),
                'user_assigned_id' => $request->user_assigned_id,
                'deadline' => Carbon::parse($request->deadline . " " . $request->contact_time . ":00"),
                'status_id' => $request->status_id,
                'user_created_id' => auth()->id(),
                'external_id' => Uuid::uuid4()->toString(),
                'client_id' => $client->id
            ]
        );
        $insertedExternalId = $lead->external_id;
        Session()->flash('flash_message', __('Lead successfully added'));

        event(new \App\Events\LeadAction($lead, self::CREATED));
        Session()->flash('flash_message', __('Lead successfully added'));
        return redirect()->route('leads.show', $insertedExternalId);
    }

    public function destroy(Lead $lead, Request $request)
    {
        $deleteInvoice = $request->delete_invoice ? true : false;
        if($lead->invoice && $deleteInvoice) {
            $lead->invoice()->delete();
        } elseif($lead->invoice) {
            $lead->invoice->removeReference();
        }
        $lead->delete();
        
        Session()->flash('flash_message', __('Lead deleted'));
        return redirect()->back();
    }

    public function updateAssign($external_id, Request $request)
    {
        $lead = $this->findByExternalId($external_id);
        $input = $request->get('user_assigned_id');
        $input = array_replace($request->all());
        $lead->fill($input)->save();
        $insertedName = $lead->user->name;

        event(new \App\Events\LeadAction($lead, self::UPDATED_ASSIGN));
        Session()->flash('flash_message', __('New user is assigned'));
        return redirect()->back();
    }

    /**
     * Update the follow up date (Deadline)
     * @param UpdateLeadFollowUpRequest $request
     * @param $external_id
     * @return mixed
     */
    public function updateFollowup(UpdateLeadFollowUpRequest $request, $external_id)
    {
        if (!auth()->user()->can('lead-update-deadline')) {
            session()->flash('flash_message_warning', __('You do not have permission to change task deadline'));
            return redirect()->route('tasks.show', $external_id);
        }
        $lead = $this->findByExternalId($external_id);
        $lead->fill(['deadline' => Carbon::parse($request->deadline . " " . $request->contact_time . ":00")])->save();
        event(new \App\Events\LeadAction($lead, self::UPDATED_DEADLINE));
        Session()->flash('flash_message', __('New follow up date is set'));
        return redirect()->back();
    }

    /**
     * Display the specified resource.
     *
     * @param  int $external_id
     * @return \Illuminate\Http\Response
     */
    public function show($external_id)
    {
        return view('leads.show')
            ->withLead($this->findByExternalId($external_id))
            ->withUsers(User::with(['department'])->get()->pluck('nameAndDepartmentEagerLoading', 'id'))
            ->withCompanyname(Setting::first()->company)
            ->withStatuses(Status::typeOfLead()->pluck('title', 'id'));
    }

    /**
     * Complete lead
     * @param $external_id
     * @param Request $request
     * @return mixed
     */
    public function updateStatus($external_id, Request $request)
    {
        if (!auth()->user()->can('lead-update-status')) {
            session()->flash('flash_message_warning', __('You do not have permission to change lead status'));
            return redirect()->route('tasks.show', $external_id);
        }
        $lead = $this->findByExternalId($external_id);
        if (isset($request->closeLead) && $request->closeLead === true) {
            $lead->status_id = Status::typeOfLead()->where('title', 'Closed')->first()->id;
            $lead->save();
        } else {
            $lead->fill($request->all())->save();
        }
        event(new \App\Events\LeadAction($lead, self::UPDATED_STATUS));
        Session()->flash('flash_message', __('Lead status updated'));
        return redirect()->back();
    }

    public function convertToQualifiedLead(Lead $lead)
    {
        Session()->flash('flash_message', __('Lead status updated'));
        return $lead->convertToQualified();
    }


    public function convertToOrder(Lead $lead)
    {
        $invoice = $lead->convertToOrder();
        return $invoice->external_id;
    }
    /**
     * @param $external_id
     * @return mixed
     */
    public function findByExternalId($external_id)
    {
        return Lead::whereExternalId($external_id)->first();
    }
}
