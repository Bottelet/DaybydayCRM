<?php
namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Tasks;
use App\User;
use App\Client;
use Illuminate\Http\Request;
use Gate;
use App\TaskTime;
use Datatables;
use Carbon;
use App\Dinero;
use App\Billy;
use App\Integration;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTimeTaskRequest;
use App\Repositories\Task\TaskRepositoryContract;
use App\Repositories\User\UserRepositoryContract;
use App\Repositories\Client\ClientRepositoryContract;
use App\Repositories\Setting\SettingRepositoryContract;
use App\Repositories\Invoice\InvoiceRepositoryContract;


class TasksController extends Controller
{

    protected $request;
    protected $tasks;
    protected $clients;
    protected $settings;
    protected $users;
    protected $invoices;
    
    public function __construct(
        TaskRepositoryContract $tasks,
        UserRepositoryContract $users,
        ClientRepositoryContract $clients,
        InvoiceRepositoryContract $invoices,
        SettingRepositoryContract $settings
    ) {
    
        $this->tasks = $tasks;
        $this->users = $users;
        $this->clients = $clients;
        $this->invoices = $invoices;
        $this->settings = $settings;

        $this->middleware('task.create', ['only' => ['create']]);
        $this->middleware('task.update.status', ['only' => ['updateStatus']]);
        $this->middleware('task.assigned', ['only' => ['updateAssign', 'updateTime']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return view('tasks.index');
    }

    public function anyData()
    {
        $tasks = Tasks::select(
            ['id', 'title', 'created_at', 'deadline', 'fk_user_id_assign']
        )
        ->where('status', 1)->get();
        return Datatables::of($tasks)
        ->addColumn('titlelink', function ($tasks) {
                return '<a href="tasks/'.$tasks->id.'" ">'.$tasks->title.'</a>';
        })
        ->editColumn('created_at', function ($tasks) {
                return $tasks->created_at ? with(new Carbon($tasks->created_at))
                ->format('d/m/Y') : '';
        })
        ->editColumn('deadline', function ($tasks) {
                return $tasks->created_at ? with(new Carbon($tasks->created_at))
                ->format('d/m/Y') : '';
        })
        ->editColumn('fk_user_id_assign', function ($tasks) {
                return $tasks->assignee->name;
        })->make(true);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return view('tasks.create')
        ->withUsers($this->users->getAllUsersWithDepartments())
        ->withClients($this->clients->listAllClients());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(StoreTaskRequest $request) // uses __contrust request
    {
        $getInsertedId = $this->tasks->create($request);
        return redirect()->route("tasks.show", $getInsertedId);
    }

   

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show(Request $request, $id)
    {
        $invoiceContacts = array();
        $apiConnected = false;

        
        $integrationCheck = Integration::first();       
        $api = Integration::getApi('billing');
     
        
        if($api){
            $apiConnected = true;
            $invoiceContacts = $api->getContacts();
        }else{
            $apiConnected = false;
            $invoiceContacts = array();
        }
        
		
        
        return view('tasks.show')
        ->withTasks($this->tasks->find($id))
        ->withUsers($this->users->getAllUsersWithDepartments())
        ->withContacts($invoiceContacts)
        ->withTasktimes($this->tasks->getTaskTime($id))
        ->withCompanyname($this->settings->getCompanyName())
        ->withApiconnected($apiConnected);
    }


/**
 * Sees if the Settings from backend allows all to complete taks
 * or only assigned user. if only assigned user:
 * @param  [Auth]  $id Checks Logged in users id
 * @param  [Model] $task->fk_user_id_assign Checks the id of the user assigned to the task
 * If Auth and fk_user_id allow complete else redirect back if all allowed excute
     * else stmt*/
    public function updateStatus($id, Request $request)
    {
        $this->tasks->updateStatus($id, $request);
        Session()->flash('flash_message', 'Task is completed');
        return redirect()->back();
    }


    public function updateAssign($id, Request $request)
    {
        $clientId = $this->tasks->getAssignedClient($id)->id;

        
        $this->tasks->updateAssign($id, $request);
        Session()->flash('flash_message', 'New user is assigned');
        return redirect()->back();
    }

    public function updateTime($id, Request $request)
    {
        $this->tasks->updateTime($id, $request);
        Session()->flash('flash_message', 'Time has been updated');
        return redirect()->back();
    }

    public function invoice($id, Request $request)
    {
        
        $this->tasks->invoice($id, $request);
        Session()->flash('flash_message', 'Invoice created');
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function marked()
    {
        Notifynder::readAll(\Auth::id());
        return redirect()->back();
    }
}
