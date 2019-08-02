<?php

namespace App\Http\Controllers;

use App\Http\Requests\Client\StoreClientRequest;
use App\Http\Requests\Client\UpdateClientRequest;
use App\Models\Client;
use App\Repositories\Client\ClientRepositoryContract;
use App\Repositories\Setting\SettingRepositoryContract;
use App\Repositories\User\UserRepositoryContract;
use Datatables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClientsController extends Controller
{
    protected $users;
    protected $clients;
    protected $settings;

    public function __construct(
        UserRepositoryContract $users,
        ClientRepositoryContract $clients,
        SettingRepositoryContract $settings
    ) {
        $this->users    = $users;
        $this->clients  = $clients;
        $this->settings = $settings;
        $this->middleware('client.create', ['only' => ['create']]);
        $this->middleware('client.update', ['only' => ['edit']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('clients.index');
    }

    /**
     * Display a listing of the resource.
     */
    public function my()
    {
        return view('clients.my');
    }

    /**
     * Make json respnse for datatables.
     *
     * @return mixed
     */
    public function anyData()
    {
        $clients = Client::select(['clients.*', DB::raw('users.name AS salesperson')])->join('users', 'clients.user_id', '=', 'users.id');

        // $clients = Client::with('user')->select('clients.*');

        $dt = Datatables::of($clients)
            ->addColumn('namelink', function ($clients) {
                return '<a href="'.route('clients.show', $clients->id).'">'.$clients->name.'</a>';
            })
            ->addColumn('emaillink', function ($clients) {
                return '<a href="mailto:'.$clients->primary_email.'">'.$clients->primary_email.'</a>';
            });

        // this looks wierd, but in order to keep the two buttons on the same line
        // you have to put them both within the form tags if the Delete button is
        // enabled
        $actions = '';
        if (Auth::user()->can('client-delete')) {
            $actions .= '<form action="{{ route(\'clients.destroy\', $id) }}" method="POST">
            ';
        }
        if (Auth::user()->can('client-update')) {
            $actions .= '<a href="{{ route(\'clients.edit\', $id) }}" class="btn btn-xs btn-success" >Edit</a>';
        }
        if (Auth::user()->can('client-delete')) {
            $actions .= '
                <input type="hidden" name="_method" value="DELETE">
                <input type="submit" name="submit" value="Delete" class="btn btn-danger btn-xs" onClick="return confirm(\'Are you sure?\')"">
                {{csrf_field()}}
            </form>';
        }

        return $dt->addColumn('actions', $actions)->make(true);
    }

    /**
     * Make json respnse for datatables.
     *
     * @return mixed
     */
    public function myData()
    {
        $clients = Client::with('user')->select('clients.*')->my();

        $dt = Datatables::of($clients)
            ->addColumn('namelink', function ($clients) {
                return '<a href="'.route('clients.show', $clients->id).'">'.$clients->name.'</a>';
            })
            ->addColumn('emaillink', function ($clients) {
                return '<a href="mailto:'.$clients->primary_email.'">'.$clients->primary_email.'</a>';
            });

        // this looks wierd, but in order to keep the two buttons on the same line
        // you have to put them both within the form tags if the Delete button is
        // enabled
        $actions = '';
        if (Auth::user()->can('client-delete')) {
            $actions .= '<form action="{{ route(\'clients.destroy\', $id) }}" method="POST">
            ';
        }
        if (Auth::user()->can('client-update')) {
            $actions .= '<a href="{{ route(\'clients.edit\', $id) }}" class="btn btn-xs btn-success" >Edit</a>';
        }
        if (Auth::user()->can('client-delete')) {
            $actions .= '
                <input type="hidden" name="_method" value="DELETE">
                <input type="submit" name="submit" value="Delete" class="btn btn-danger btn-xs" onClick="return confirm(\'Are you sure?\')"">
                {{csrf_field()}}
            </form>';
        }

        return $dt->addColumn('actions', $actions)->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return mixed
     */
    public function create()
    {
        return view('clients.create')
            ->withUsers($this->users->getAllUsersWithDepartments())
            ->withIndustries($this->clients->listAllIndustries());
    }

    /**
     * @param StoreClientRequest $request
     *
     * @return mixed
     */
    public function store(StoreClientRequest $request)
    {
        $this->clients->create($request->all());

        return redirect()->route('clients.index');
    }

    /**
     * @param Request $vatRequest
     *
     * @return mixed
     */
    public function cvrapiStart(Request $vatRequest)
    {
        return redirect()->back()
            ->with('data', $this->clients->vat($vatRequest));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return mixed
     */
    public function show($id)
    {
        return view('clients.show')
            ->withClient($this->clients->find($id))
            ->withInvoices($this->clients->getInvoices($id))
            ->withUsers($this->users->getAllUsersWithDepartments());
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return mixed
     */
    public function edit($id)
    {
        return view('clients.edit')
            ->withClient($this->clients->find($id))
            ->withUsers($this->users->getAllUsersWithDepartments())
            ->withIndustries($this->clients->listAllIndustries());
    }

    /**
     * @param $id
     * @param UpdateClientRequest $request
     *
     * @return mixed
     */
    public function update($id, UpdateClientRequest $request)
    {
        $this->clients->update($id, $request);
        Session()->flash('flash_message', 'Client successfully updated');

        return redirect()->route('clients.index');
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function destroy($id)
    {
        $this->clients->destroy($id);

        return redirect()->route('clients.index');
    }

    /**
     * @param $id
     * @param Request $request
     *
     * @return mixed
     */
    public function updateAssign($id, Request $request)
    {
        $this->clients->updateAssign($id, $request);
        Session()->flash('flash_message', 'New user is assigned');

        return redirect()->back();
    }
}
