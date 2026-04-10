<?php

namespace App\Http\Controllers;

use App\Events\LeadAction;
use App\Http\Requests\Lead\StoreLeadRequest;
use App\Http\Requests\Lead\UpdateLeadFollowUpRequest;
use App\Models\Client;
use App\Models\Lead;
use App\Models\Setting;
use App\Models\Status;
use App\Models\User;
use App\Services\Invoice\InvoiceCalculator;
use Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
        $this->middleware(function ($request, $next) {
            if (! auth()->check() || ! auth()->user()->can('lead-delete')) {
                abort(403);
            }

            return $next($request);
        }, ['only' => ['destroy', 'destroyJson']]);
    }

    public function index()
    {
        return view('leads.index')
            ->withStatuses(Status::typeOfLead()->get());
    }

    /**
     * Data for Data tables
     *
     * @return mixed
     */
    public function leadsJson()
    {
        $leads = Lead::with(['user', 'creator', 'client.primaryContact', 'status'])->get();

        $leads->map(function ($item) {
            return [$item['visible_deadline_date'] = $item['deadline']->format(carbonDate()), $item['visible_deadline_time'] = $item['deadline']->format(carbonTime())];
        });

        return $leads->toJson();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create($client_external_id = null)
    {
        $client = Client::whereExternalId($client_external_id);

        return view('leads.create')
            ->withUsers(User::with(['department'])->get()->pluck('nameAndDepartmentEagerLoading', 'id'))
            ->withClients(Client::pluck('company_name', 'external_id'))
            ->withClient($client ?: null)
            ->withStatuses(Status::typeOfLead()->pluck('title', 'id'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreLeadRequest|Request  $request
     * @return Response
     */
    public function store(StoreLeadRequest $request)
    {
        if ($request->client_external_id) {
            $client = Client::whereExternalId($request->client_external_id)->first();
        }

        $lead = Lead::create(
            [
                'title' => $request->title,
                'description' => clean($request->description),
                'user_assigned_id' => $request->user_assigned_id,
                'deadline' => Carbon::parse($request->deadline.' '.$request->contact_time.':00'),
                'status_id' => $request->status_id,
                'user_created_id' => auth()->id(),
                'external_id' => Uuid::uuid4()->toString(),
                'client_id' => $client->id,
            ]
        );

        event(new LeadAction($lead, self::CREATED));
        session()->flash('flash_message', __('Lead successfully added'));

        return redirect()->route('leads.show', $lead->external_id);
    }

    public function destroy(Lead $lead, Request $request)
    {
        if (! auth()->user()->can('lead-delete')) {
            session()->flash('flash_message_warning', __('You do not have permission to delete leads'));

            return redirect()->back();
        }

        $deleteOffers = $request->delete_offers ? true : false;
        if ($lead->offers && $deleteOffers) {
            $lead->offers()->delete();
        } elseif ($lead->offers) {
            foreach ($lead->offers as $offer) {
                $offer->update([
                    'source_id' => null,
                    'source_type' => null,
                ]);
            }
        }

        $lead->delete();

        session()->flash('flash_message', __('Lead deleted'));

        return redirect()->back();
    }

    public function destroyJson(Lead $lead, Request $request)
    {
        if (! auth()->user()->can('lead-delete')) {
            return response('Access denied', 403);
        }

        $deleteOffers = $request->delete_offers ? true : false;
        if ($lead->offers && $deleteOffers) {
            $lead->offers()->delete();
        } elseif ($lead->offers) {
            foreach ($lead->offers as $offer) {
                $offer->update([
                    'source_id' => null,
                    'source_type' => null,
                ]);
            }
        }

        $lead->delete();

        return response('OK');
    }

    public function updateAssign($external_id, Request $request)
    {
        $lead = $this->findByExternalId($external_id);
        $input = $request->only(['user_assigned_id']);
        $lead->fill($input)->save();

        event(new LeadAction($lead, self::UPDATED_ASSIGN));
        Session()->flash('flash_message', __('New user is assigned'));

        return redirect()->back();
    }

    /**
     * Update the follow up date (Deadline)
     *
     * @return mixed
     */
    public function updateFollowup(UpdateLeadFollowUpRequest $request, $external_id)
    {
        if (! auth()->user()->can('lead-update-deadline')) {
            session()->flash('flash_message_warning', __('You do not have permission to change task deadline'));

            return redirect()->route('tasks.show', $external_id);
        }
        $lead = $this->findByExternalId($external_id);
        $lead->fill(['deadline' => Carbon::parse($request->deadline.' '.$request->contact_time.':00')->toDateTimeString()])->save();
        event(new LeadAction($lead, self::UPDATED_DEADLINE));
        Session()->flash('flash_message', __('New follow up date is set'));

        return redirect()->back();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $external_id
     * @return Response
     */
    public function show($external_id)
    {
        $lead = $this->findByExternalId($external_id);

        $offers = $lead->offers->map(function ($offer) {
            return new InvoiceCalculator($offer);
        });

        return view('leads.show')
            ->withLead($lead)
            ->withOffers($offers)
            ->withUsers(User::with(['department'])->get()->pluck('nameAndDepartmentEagerLoading', 'id'))
            ->withCompanyname(Setting::first()->company)
            ->withStatuses(Status::typeOfLead()->pluck('title', 'id'));
    }

    /**
     * Complete lead
     *
     * @return mixed
     */
    public function updateStatus($external_id, Request $request)
    {
        if (! auth()->user()->can('lead-update-status')) {
            session()->flash('flash_message_warning', __('You do not have permission to change lead status'));

            return redirect()->route('leads.show', $external_id);
        }
        $lead = $this->findByExternalId($external_id);
        if (isset($request->closeLead) && $request->closeLead === true) {
            $lead->status_id = Status::typeOfLead()->where('title', 'Closed')->first()->id;
            $lead->save();
        } elseif (isset($request->openLead) && $request->openLead === true) {
            $lead->status_id = Status::typeOfLead()->where('title', 'Open')->first()->id;
            $lead->save();
        } else {
            $statusId = $request->input('status_id');
            // Validate that the status_id belongs to lead statuses
            $validStatus = Status::typeOfLead()->where('id', $statusId)->exists();
            if (! $validStatus) {
                session()->flash('flash_message_warning', __('Invalid status for lead'));

                return redirect()->back();
            }
            $lead->fill($request->only(['status_id']))->save();
        }
        event(new LeadAction($lead, self::UPDATED_STATUS));
        Session()->flash('flash_message', __('Lead status updated'));

        return redirect()->back();
    }

    public function convertToOrder(Lead $lead)
    {
        $invoice = $lead->convertToOrder();

        return $invoice->external_id;
    }

    /**
     * @return mixed
     */
    public function findByExternalId($external_id)
    {
        return Lead::whereExternalId($external_id)->firstOrFail();
    }
}
