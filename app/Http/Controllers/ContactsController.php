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

        return Datatables::of($contacts)
            ->addColumn('namelink', function ($contacts) {
                return '<a href="contacts/'.$contacts->id.'" ">'.$contacts->name.'</a>';
            })
            ->add_column('edit', '
                <a href="{{ route(\'contacts.edit\', $id) }}" class="btn btn-success" >Edit</a>')
            ->add_column('delete', '
                <form action="{{ route(\'contacts.destroy\', $id) }}" method="POST">
            <input type="hidden" name="_method" value="DELETE">
            <input type="submit" name="submit" value="Delete" class="btn btn-danger" onClick="return confirm(\'Are you sure?\')"">

            {{csrf_field()}}
            </form>')
            ->make(true);
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
