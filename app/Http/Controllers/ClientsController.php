<?php
namespace App\Http\Controllers;

use App\Enums\Country;
use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Status;
use App\Models\Task;
use App\Repositories\FilesystemIntegration\FilesystemIntegration;
use App\Repositories\Money\MoneyConverter;
use App\Services\ClientNumber\ClientNumberService;
use App\Services\Invoice\InvoiceCalculator;
use App\Services\Search\SearchService;
use App\Services\Storage\GetStorageProvider;
use Carbon\Carbon;
use Config;
use Dinero;
use Datatables;
use App\Models\Client;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Http\Requests\Client\StoreClientRequest;
use App\Http\Requests\Client\UpdateClientRequest;
use App\Models\User;
use App\Models\Integration;
use App\Models\Industry;
use Ramsey\Uuid\Uuid;
use App\Models\Contact;

class ClientsController extends Controller
{
    const CREATED = 'created';
    const UPDATED_ASSIGN = 'updated_assign';

    protected $users;
    protected $clients;
    protected $settings;
    /**
     * @var FilesystemIntegration
     */
    private $filesystem;

    public function __construct()
    {
        $this->middleware('client.create', ['only' => ['create']]);
        $this->middleware('client.update', ['only' => ['edit']]);
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
     * Make json respnse for datatables
     * @return mixed
     */
    public function anyData()
    {
        $clients = Client::select(['external_id', 'company_name', 'vat', 'address']);
        return Datatables::of($clients)       
            ->addColumn('namelink', '<a href="{{ route("clients.show",[$external_id]) }}">{{$company_name}}</a>')
            ->addColumn('view', '
                <a href="{{ route(\'clients.show\', $external_id) }}" class="btn btn-link" >'  . __('View') . '</a>')
            ->addColumn('edit', '
                <a href="{{ route(\'clients.edit\', $external_id) }}" class="btn btn-link" >'  . __('Edit') . '</a>')
            ->addColumn('delete', '
                <form action="{{ route(\'clients.destroy\', $external_id) }}" method="POST">
            <input type="hidden" name="_method" value="DELETE">
            <input type="submit" name="submit" value="' . __('Delete') . '" class="btn btn-link" onClick="return confirm(\'Are you sure? All the clients tasks, leads, projects, etc will be deleted as well\')"">
            {{csrf_field()}}
            </form>')
            ->rawColumns(['namelink', 'view','edit', 'delete'])
            ->make(true);
    }



    public function taskDataTable($external_id)
    {
        $client = Client::where('external_id', $external_id)->firstOrFail();
        $tasks = $client->tasks()->with(['status'])->select(
            ['id', 'external_id', 'title', 'created_at', 'deadline', 'user_assigned_id', 'client_id', 'status_id']
        )->get();


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
                return '<span class="label label-success" style="background-color:' . $tasks->status->color . '"> ' .$tasks->status->title . '</span>';
            })
            ->editColumn('assigned', function ($tasks) {
                return $tasks->assigned_user->name;
            })
            ->rawColumns(['titlelink','status_id'])
            ->make(true);
    }

    public function projectDataTable($external_id)
    {
        $client = Client::where('external_id', $external_id)->firstOrFail();
        $projects = $client->projects()->with(['status'])->select(
            ['id', 'external_id', 'title', 'created_at', 'deadline', 'user_assigned_id', 'client_id', 'status_id']
        )->get();

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
                return '<span class="label label-success" style="background-color:' . $projects->status->color . '"> ' .$projects->status->title . '</span>';
            })
            ->editColumn('assigned', function ($projects) {
                return $projects->assignee->name;
            })
            ->rawColumns(['titlelink','status_id'])
            ->make(true);
    }

    public function leadDataTable($external_id)
    {
        $client = Client::where('external_id', $external_id)->firstOrFail();
        $leads = $client->leads()->with(['status'])->select(
            ['id', 'external_id', 'title', 'created_at', 'deadline', 'user_assigned_id', 'client_id', 'status_id']
        )->get();
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
                return '<span class="label label-success" style="background-color:' . $leads->status->color . '"> ' .
                    $leads->status->title . '</span>';
            })
            ->editColumn('assigned', function ($leads) {
                return $leads->assigned_user->name;
            })
            ->rawColumns(['titlelink','status_id'])
            ->make(true);
    }

    public function invoiceDataTable($external_id)
    {
        $client = Client::where('external_id', $external_id)->firstOrFail();

        $invoices = $client->invoices()->select(
            ['id', 'external_id', 'sent_at', 'status', 'invoice_number']
        );

        return Datatables::of($invoices)
            ->editColumn('invoice_number', function ($invoices) {
                return '<a href="' . url('invoices', $invoices->external_id) . '">' . ($invoices->invoice_number ?: 'X') . '</a>';
            })
            ->editColumn('total_amount', function ($invoices) {
                $totalPrice = app(InvoiceCalculator::class, ['invoice' => $invoices])->getTotalPrice();
                return app(MoneyConverter::class, ['money' => $totalPrice])->format();
            })
            ->editColumn('invoice_sent', function ($invoices) {
                return $invoices->sent_at ? __('yes'): __('no');
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
        return view('clients.create')
            ->withUsers(User::with('department')->get()->pluck('nameAndDepartmentEagerLoading', 'id'))
            ->withIndustries($this->listAllIndustries())
            ->withCountry(Country::fromCode(Setting::first()->country));
    }

    /**
     * @param StoreClientRequest $request
     * @return mixed
     */
    public function store(StoreClientRequest $request)
    {
        $client = Client::create([
            'external_id' => Uuid::uuid4()->toString(),
            'vat' => $request->vat,
            'company_name' => $request->company_name,
            'address' => $request->address,
            'zipcode' => $request->zipcode,
            'city' => $request->city,
            'company_type' => $request->company_type,
            'industry_id' => $request->industry_id,
            'user_id' => $request->user_id,
            'client_number' => app(ClientNumberService::class)->setNextClientNumber(),
        ]);

        $contact = Contact::create([
            'external_id' => Uuid::uuid4()->toString(),
            'name' => $request->name,
            'email' => $request->email,
            'primary_number' => $request->primary_number,
            'secondary_number' => $request->secondary_number,
            'client_id' => $client->id,
            'is_primary' => true
        ]);

        Session()->flash('flash_message', __('Client successfully added'));
        event(new \App\Events\ClientAction($client, self::CREATED));
        return redirect()->route('clients.index');
    }

    /**
     * @param Request $vatRequest
     * @return mixed
     */
    public function cvrapiStart(Request $request)
    {
        $vat = $request->input('vat');

        $country = $request->input('country');
        $company_name = $request->input('company_name');

        // Strip all other characters than numbers
        $vat = preg_replace('/[^0-9]/', '', $vat);

        $result = $this->cvrApi($vat, 'dk');


        return redirect()->back()
            ->with('data', $result);
    }

    public function cvrApi($vat)
    {
        if (empty($vat)) {
            // Print error message

            return ('Please insert VAT');
        } else {
            // Start cURL
            $ch = curl_init();

            // Set cURL options
            curl_setopt($ch, CURLOPT_URL, 'http://cvrapi.dk/api?search=' . $vat . '&country=dk');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Daybyday');

            // Parse result
            $result = curl_exec($ch);

            // Close connection when done
            curl_close($ch);

            // Return our decoded result
            return json_decode($result, 1);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $external_id
     * @return mixed
     */
    public function show($external_id)
    {
        $client = $this->findByExternalId($external_id);
        //dd($client->appointments);
        return view('clients.show')
            ->withClient($client)
            ->withCompanyname(Setting::first()->company)
            ->withInvoices($this->getInvoices($client))
            ->withUsers(User::with('department')->get()->pluck('nameAndDepartmentEagerLoading', 'id'))
            ->with('filesystem_integration', Integration::whereApiType('file')->first())
            ->with('documents', $client->documents()->where('integration_type', get_class(GetStorageProvider::getStorage()))->get())
            ->with('lead_statuses', Status::typeOfLead()->get())
            ->with('task_statuses', Status::typeOfTask()->get())
            ->withRecentAppointments($client->appointments()->orderBy('start_at', 'desc')->where('end_at', '>', now()->subMonths(3))->limit(7)->get());
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $external_id
     * @return mixed
     */
    public function edit($external_id)
    {
        $client = $this->findByExternalId($external_id);
        $contact = $client->primaryContact;
        $client = (object)array_merge($contact->toArray(), $client->toArray());

        return view('clients.edit')
            ->withClient($client)
            ->withUsers(User::with('department')->get()->pluck('nameAndDepartmentEagerLoading', 'id'))
            ->withIndustries($this->listAllIndustries());
    }

    /**
     * @param $external_id
     * @param UpdateClientRequest $request
     * @return mixed
     */
    public function update($external_id, UpdateClientRequest $request)
    {
        $client = $this->findByExternalId($external_id);
        $client->fill([
            'vat' => $request->vat,
            'company_name' => $request->company_name,
            'address' => $request->address,
            'zipcode' => $request->zipcode,
            'city' => $request->city,
            'company_type' => $request->company_type,
            'industry_id' => $request->industry_id,
            'user_id' => $request->user_id,
            ])->save();

        $client->primaryContact->fill([
            'name' => $request->name,
            'email' => $request->email,
            'primary_number' => $request->primary_number,
            'secondary_number' => $request->secondary_number,
            'client_id' => $client->id,
            'is_primary' => true
        ])->save();

        Session()->flash('flash_message', __('Client successfully updated'));
        return redirect()->route('clients.index');
    }

    /**
     * @param $external_id
     * @return mixed
     */
    public function destroy($external_id)
    {
        try {
            $client = $this->findByExternalId($external_id);
            $client->delete();
            Session()->flash('flash_message', __('Client successfully deleted'));
        } catch (\Exception $e) {
            Session()->flash('flash_message_warning', __('Client could not be deleted, contact Daybyday support'));
        }

        return redirect()->route('clients.index');
    }

    /**
     * @param $external_id
     * @param Request $request
     * @return mixed
     */
    public function updateAssign($external_id, Request $request)
    {
        if (!auth()->user()->can('client-update')) {
            Session()->flash('flash_message_warning', __("Not authorized"));
            return back();
        }

        $user = User::where('external_id', $request->user_external_id)->first();
        $client = Client::with('user')->where('external_id', $external_id)->first();
        $client->updateAssignee($user);

        Session()->flash('flash_message', __('New user is assigned'));
        return redirect()->back();
    }


    /**
     * @param $client
     * @return mixed
     */
    public function getInvoices($client)
    {
        $invoice = $client->invoices()->with('invoiceLines')->get();

        return $invoice;
    }

    public function findByExternalId($external_id)
    {
        return Client::where('external_id', $external_id)->firstOrFail();
    }

    /**
     * @return mixed
     */
    public function listAllClients()
    {
        return Client::pluck('company_name', 'id');
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
        return Industry::pluck('name', 'id');
    }
}
