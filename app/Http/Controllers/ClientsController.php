<?php

namespace App\Http\Controllers;

use App\Enums\Country;
use App\Enums\InvoiceStatus;
use App\Events\ClientAction;
use App\Http\Requests\Client\StoreClientRequest;
use App\Http\Requests\Client\UpdateClientRequest;
use App\Models\Client;
use App\Models\Industry;
use App\Models\Integration;
use App\Models\Setting;
use App\Models\Status;
use App\Models\User;
use App\Repositories\FilesystemIntegration\FilesystemIntegration;
use App\Repositories\Money\MoneyConverter;
use App\Services\Client\ClientService;
use App\Services\Invoice\InvoiceCalculator;
use App\Services\Storage\GetStorageProvider;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

class ClientsController extends Controller
{
    public const CREATED = 'created';

    public const UPDATED_ASSIGN = 'updated_assign';

    protected $users;

    protected $clients;

    protected $settings;

    /**
     * @var FilesystemIntegration
     */
    private $filesystem;

    public function __construct(private ClientService $clientService)
    {
        $this->middleware('client.create', ['only' => ['create']]);
        $this->middleware('client.update', ['only' => ['edit']]);
        $this->middleware('client.delete', ['only' => ['destroy']]);
        $this->middleware('is.demo', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('clients.index');
    }

    /**
     * Make json respnse for datatables.
     *
     * @return mixed
     */
    public function anyData()
    {
        $clients = $this->clientService->getClientsForDataTable();

        return Datatables::of($clients)
            ->addColumn('namelink', '<a href="{{ route("clients.show",[$external_id]) }}">{{$company_name}}</a>')
            ->addColumn('view', '
                <a href="{{ route(\'clients.show\', $external_id) }}" class="btn btn-link" >' . __('View') . '</a>')
            ->addColumn('edit', '
                <a href="{{ route(\'clients.edit\', $external_id) }}" class="btn btn-link" >' . __('Edit') . '</a>')
            ->addColumn('delete', '
                <form action="{{ route(\'clients.destroy\', $external_id) }}" method="POST">
            <input type="hidden" name="_method" value="DELETE">
            <input type="submit" name="submit" value="' . __('Delete') . '" class="btn btn-link" onClick="return confirm(\'Are you sure? All the clients tasks, leads, projects, etc will be deleted as well\')"">
            {{csrf_field()}}
            </form>')
            ->rawColumns(['namelink', 'view', 'edit', 'delete'])
            ->make(true);
    }

    public function taskDataTable($external_id)
    {
        $client = $this->clientService->findByExternalId($external_id);
        $tasks  = $this->clientService->getTasksWithRelations($client);

        return Datatables::of($tasks)
            ->addColumn('titlelink', '<a href="{{ route("tasks.show",[$external_id]) }}">{{$title}}</a>')
            ->editColumn('created_at', function ($tasks) {
                return $tasks->created_at ? with(new Carbon($tasks->created_at))
                    ->format(carbonDate()) : '';
            })
            ->editColumn('deadline', function ($tasks) {
                return $tasks->deadline ? with(new Carbon($tasks->deadline))
                    ->format(carbonDate()) : '';
            })
            ->editColumn('status_id', function ($tasks) {
                return '<span class="label label-success" style="background-color:' . $tasks->status->color . '"> ' . $tasks->status->title . '</span>';
            })
            ->editColumn('assigned', function ($tasks) {
                return $tasks->assigned_user->name;
            })
            ->rawColumns(['titlelink', 'status_id'])
            ->make(true);
    }

    public function projectDataTable($external_id)
    {
        $client   = $this->clientService->findByExternalId($external_id);
        $projects = $this->clientService->getProjectsWithRelations($client);

        return Datatables::of($projects)
            ->addColumn('titlelink', '<a href="{{ route("projects.show",[$external_id]) }}">{{$title}}</a>')
            ->editColumn('created_at', function ($projects) {
                return $projects->created_at ? with(new Carbon($projects->created_at))
                    ->format(carbonDate()) : '';
            })
            ->editColumn('deadline', function ($projects) {
                return $projects->deadline ? with(new Carbon($projects->deadline))
                    ->format(carbonDate()) : '';
            })
            ->editColumn('status_id', function ($projects) {
                return '<span class="label label-success" style="background-color:' . $projects->status->color . '"> ' . $projects->status->title . '</span>';
            })
            ->editColumn('assigned', function ($projects) {
                return $projects->assignee->name;
            })
            ->rawColumns(['titlelink', 'status_id'])
            ->make(true);
    }

    public function leadDataTable($external_id)
    {
        $client = $this->clientService->findByExternalId($external_id);
        $leads  = $this->clientService->getLeadsWithRelations($client);

        return Datatables::of($leads)
            ->addColumn('titlelink', '<a href="{{ route("leads.show",[$external_id]) }}">{{$title}}</a>')
            ->editColumn('created_at', function ($leads) {
                return $leads->created_at ? with(new Carbon($leads->created_at))
                    ->format(carbonDate()) : '';
            })
            ->editColumn('deadline', function ($leads) {
                return $leads->deadline ? with(new Carbon($leads->deadline))
                    ->format(carbonDate()) : '';
            })
            ->editColumn('status_id', function ($leads) {
                return '<span class="label label-success" style="background-color:' . $leads->status->color . '"> '
                    . $leads->status->title . '</span>';
            })
            ->editColumn('assigned', function ($leads) {
                return $leads->assigned_user->name;
            })
            ->rawColumns(['titlelink', 'status_id'])
            ->make(true);
    }

    public function invoiceDataTable($external_id)
    {
        $client = $this->clientService->findByExternalId($external_id);

        $invoices = $client->invoices()->with('invoiceLines');

        return Datatables::of($invoices)
            ->editColumn('invoice_number', function ($invoices) {
                return '<a href="' . url('invoices', $invoices->external_id) . '">' . ($invoices->invoice_number ?: 'X') . '</a>';
            })
            ->editColumn('total_amount', function ($invoices) {
                $totalPrice = app(InvoiceCalculator::class, ['invoice' => $invoices])->getTotalPrice();

                return app(MoneyConverter::class, ['money' => $totalPrice])->format();
            })
            ->editColumn('invoice_sent', function ($invoices) {
                return $invoices->sent_at ? __('yes') : __('no');
            })
            ->editColumn('status', function ($invoices) {
                return __(InvoiceStatus::fromStatus($invoices->status)->getDisplayValue());
            })
            ->rawColumns(['invoice_number'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return mixed
     */
    public function create()
    {
        $setting = Setting::first();
        $country = $setting ? Country::fromCode($setting->country) : null;

        return view('clients.create')
            ->withUsers(User::with('department')->get()->pluck('nameAndDepartmentEagerLoading', 'id'))
            ->withIndustries($this->listAllIndustries())
            ->withCountry($country);
    }

    /**
     * @return mixed
     */
    public function store(StoreClientRequest $request)
    {
        $expectsJson = $this->expectsJsonResponse($request);

        try {
            [$client, $contact] = $this->clientService->createClientWithContact($request->validated());
        } catch (Throwable $exception) {
            report($exception);
            $message = __('Client could not be created. Please try again.');

            return $this->failureResponse($request, $message, 'client', 500);
        }

        event(new ClientAction($client, self::CREATED));

        if ($expectsJson) {
            return response()->json([
                'client'  => $client,
                'contact' => $contact,
                'message' => __('Client successfully added'),
            ], 201);
        }

        session()->flash('flash_message', __('Client successfully added'));

        return redirect()->route('clients.index');
    }

    /**
     * @param Request $vatRequest
     *
     * @return mixed
     */
    public function cvrapiStart(Request $request)
    {
        $vat          = $request->input('vat');
        $country      = $request->input('country');
        $company_name = $request->input('company_name');

        // Strip all other characters than numbers
        $vat = preg_replace('/[^0-9]/', '', $vat);

        $result = $this->clientService->cvrApi($vat, $country ?? 'dk');

        return redirect()->back()->with('data', $result);
    }

    /**
     * Display the specified resource.
     *
     * @param int $external_id
     *
     * @return mixed
     */
    public function show($external_id)
    {
        $client = $this->clientService->getClientWithRelations($external_id);

        // Fetch integration once and reuse for both filesystem_integration and document filtering
        $filesystemIntegration = Integration::whereApiType('file')->first();
        $storageClass          = GetStorageProvider::providerClassFromIntegration($filesystemIntegration);

        // Use already eager-loaded collections to avoid duplicate queries
        $filteredDocuments = $client->documents->filter(
            fn ($document) => $document->integration_type === $storageClass
        )->values();

        $recentAppointments = $client->appointments
            ->where('end_at', '>', now()->subMonths(3))
            ->sortByDesc('start_at')
            ->take(7)
            ->values();

        return view('clients.show')
            ->withClient($client)
            ->withCompanyname(Setting::first()->company)
            ->withInvoices($this->clientService->getInvoices($client))
            ->withUsers(User::with('department')->get()->pluck('nameAndDepartmentEagerLoading', 'id'))
            ->with('filesystem_integration', $filesystemIntegration)
            ->with('documents', $filteredDocuments)
            ->with('lead_statuses', Status::typeOfLead()->get())
            ->with('task_statuses', Status::typeOfTask()->get())
            ->withRecentAppointments($recentAppointments);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $external_id
     *
     * @return mixed
     */
    public function edit($external_id)
    {
        $client  = $this->clientService->findByExternalId($external_id);
        $contact = $client->primaryContact;
        $client  = (object) array_merge($contact->toArray(), $client->toArray());

        return view('clients.edit')
            ->withClient($client)
            ->withUsers(User::with('department')->get()->pluck('nameAndDepartmentEagerLoading', 'id'))
            ->withIndustries($this->listAllIndustries());
    }

    /**
     * @return mixed
     */
    public function update($external_id, UpdateClientRequest $request)
    {
        $client = $this->clientService->findByExternalId($external_id);
        $client->fill([
            'vat'          => $request->vat,
            'company_name' => $request->company_name,
            'address'      => $request->address,
            'zipcode'      => $request->zipcode,
            'city'         => $request->city,
            'company_type' => $request->company_type,
            'industry_id'  => $request->industry_id,
            'user_id'      => $request->user_id,
        ])->save();

        if ($client->primaryContact) {
            $client->primaryContact->fill([
                'name'             => $request->name,
                'email'            => $request->email,
                'primary_number'   => $request->primary_number,
                'secondary_number' => $request->secondary_number,
                'client_id'        => $client->id,
                'is_primary'       => true,
            ])->save();
        }

        session()->flash('flash_message', __('Client successfully updated'));

        return redirect()->route('clients.index');
    }

    /**
     * @return mixed
     */
    public function destroy($external_id)
    {
        try {
            $client = $this->clientService->findByExternalId($external_id);
            $client->delete();
            session()->flash('flash_message', __('Client successfully deleted'));
        } catch (Exception $e) {
            session()->flash('flash_message_warning', __('Client could not be deleted, contact Daybyday support'));
        }

        return redirect()->route('clients.index');
    }

    /**
     * @return mixed
     */
    public function updateAssign($external_id, Request $request)
    {
        if ( ! auth()->user()->can('client-update')) {
            session()->flash('flash_message_warning', __('Not authorized'));

            return back();
        }

        $userExternalId = $request->user_external_id ?: $request->user_assigned_id;
        $user           = User::query()->where('external_id', $userExternalId)->first();
        if ( ! $user && is_numeric($userExternalId)) {
            $user = User::query()->find($userExternalId);
        }
        $client = Client::with('user')->where('external_id', $external_id)->first();
        $client->updateAssignee($user);

        session()->flash('flash_message', __('New user is assigned'));

        return redirect()->back();
    }

    /**
     * @return mixed
     */
    public function getInvoices($client)
    {
        return $this->clientService->getInvoices($client);
    }

    public function findByExternalId($external_id)
    {
        return $this->clientService->findByExternalId($external_id);
    }

    /**
     * @return mixed
     */
    public function listAllClients()
    {
        return Client::query()->pluck('company_name', 'id');
    }

    /**
     * @return int
     */
    public function getAllClientsCount()
    {
        return Client::all()->count();
    }

    /**
     * @return mixed
     */
    public function listAllIndustries()
    {
        return Industry::query()->pluck('name', 'id');
    }
}
