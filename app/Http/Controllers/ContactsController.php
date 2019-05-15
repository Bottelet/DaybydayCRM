<?php

namespace App\Http\Controllers;

use App\Http\Requests\Contact\StoreContactRequest;
use App\Http\Requests\Contact\UpdateContactRequest;
use App\Models\Contact;
use App\Repositories\Client\ClientRepositoryContract;
use App\Repositories\Contact\ContactRepositoryContract;
use App\Repositories\Setting\SettingRepositoryContract;
use App\Repositories\User\UserRepositoryContract;
use Datatables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContactsController extends Controller
{
    protected $users;
    protected $contacts;
    protected $settings;

    public function __construct(
        UserRepositoryContract $users,
        ContactRepositoryContract $contacts,
        ClientRepositoryContract $clients,
        SettingRepositoryContract $settings
    ) {
        $this->users    = $users;
        $this->contacts = $contacts;
        $this->clients  = $clients;
        $this->settings = $settings;
        $this->middleware('contact.create', ['only' => ['create']]);
        $this->middleware('contact.update', ['only' => ['edit']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('contacts.index');
    }

    /**
     * Make json respnse for datatables.
     *
     * @return mixed
     */
    public function anyData()
    {
        $contacts = Contact::select(['id', 'name', 'job_title', 'email', 'primary_number']);

        $dt = Datatables::of($contacts)
            ->addColumn('namelink', function ($contacts) {
                return '<a href="contacts/'.$contacts->id.'" ">'.$contacts->name.'</a>';
            })
            ->addColumn('emaillink', function ($clients) {
                return '<a href="mailto:'.$clients->email.'" ">'.$clients->email.'</a>';
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
                <input type="submit" name="submit" value="Delete" class="btn btn-xs btn-danger" onClick="return confirm(\'Are you sure?\')"">
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
        return view('contacts.create')
            ->withClients($this->clients->listAllClients());
    }

    /**
     * @param StoreContactRequest $request
     *
     * @return mixed
     */
    public function store(StoreContactRequest $request)
    {
        $this->contacts->create($request->all());

        return redirect()->route('contacts.index');
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
        return view('contacts.show')
            ->withContact($this->contacts->find($id))
            ->withUsers($this->users->getAllUsersWithDepartments())
            ->withCompanyname($this->settings->getCompanyName());
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
        return view('contacts.edit')
            ->withContact($this->contacts->find($id));
    }

    /**
     * @param $id
     * @param UpdateContactRequest $request
     *
     * @return mixed
     */
    public function update($id, UpdateContactRequest $request)
    {
        $this->contacts->update($id, $request);
        Session()->flash('flash_message', 'Contact successfully updated');

        return redirect()->route('contacts.index');
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function destroy($id)
    {
        $this->contacts->destroy($id);

        return redirect()->route('contacts.index');
    }
}
