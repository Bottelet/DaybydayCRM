<?php
namespace App\Repositories\Task;

use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use Carbon;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use Illuminate\Support\Facades\DB;
use App\Models\Integration;
use App\Models\Activity;
use Ramsey\Uuid\Uuid;

/**
 * Class TaskRepository
 * @package App\Repositories\Task
 */
class TaskRepository implements TaskRepositoryContract
{
    const CREATED = 'created';
    const UPDATED_STATUS = 'updated_status';
    const UPDATED_TIME = 'updated_time';
    const UPDATED_ASSIGN = 'updated_assign';
    const UPDATED_DEADLINE = 'updated_deadline';

    /**
     * @param $id
     * @return mixed
     */
    public function find($id)
    {
        return Task::findOrFail($id);
    }

        /**
     * @param $id
     * @return mixed
     */
    public function findByExternalId($external_id)
    {
        return Task::whereExternalId($external_id)->first();
    }

    public function getInvoiceLines($id)
    {
        
    }

    /**
     * @param $external_id
     * @return mixed
     */
    public function getAssignedClient($external_id)
    {
        $tasks = $this->findByExternalId($external_id);
        $tasks->client;
        return $tasks;
    }


    /**
     * @param $requestData
     * @return mixed
     */
    public function create($requestData)
    {
        $input = $requestData = array_merge(
            $requestData->all(),
            ['user_created_id' => auth()->id(),
            'external_id' => Uuid::uuid4()->toString()]
        );

        $task = Task::create($input);

        $insertedExternalId = $task->external_id;
        Session()->flash('flash_message', 'Task successfully added!');
        event(new \App\Events\TaskAction($task, self::CREATED));

        return $insertedExternalId;
    }

    /**
     * @param $external_id
     * @param $requestData
     */
    public function updateStatus($external_id, $requestData)
    {
        $task = $this->findByExternalId($external_id);
        $task->fill($requestData->all())->save();
        event(new \App\Events\TaskAction($task, self::UPDATED_STATUS));
    }

    /**
     * @param $id
     * @param $request
     */
    public function updateTime($external_id, $request)
    {
        $task = $this->findByExternalId($external_id);
         
        $invoice = $task->invoice;
        if(!$invoice) {
            $invoice = Invoice::create([
                'status' => 'draft',
                'client_id' => $task->client->id,
                'external_id' =>  Uuid::uuid4()->toString()
            ]);
            $task->invoice_id = $invoice->id;
            $task->save();
        } 

        InvoiceLine::create([
                'title' => $request->title,
                'comment' => $request->comment,
                'quantity' => $request->quantity,
                'type' => $request->type,
                'price' => $request->price,
                'invoice_id' => $invoice->id
        ]);

        event(new \App\Events\TaskAction($task, self::UPDATED_TIME));
    }

    /**
     * @param $external_id
     * @param $requestData
     */
    public function updateAssign($external_id, $requestData)
    {
        $task = Task::with('user')->whereExternalId($external_id)->first();

        $input = $requestData->get('user_assigned_id');

        $input = array_replace($requestData->all());
        $task->fill($input)->save();
        $task = $task->fresh();

        event(new \App\Events\TaskAction($task, self::UPDATED_ASSIGN));
    }

    /**
     * @param $external_id
     * @param $requestData
     */
    public function updateDeadline($external_id, $requestData)
    {
        $task = $this->findByExternalId($external_id);
        $input = $requestData->all();
        $input = $requestData =
            ['deadline' => $requestData->deadline_date . " " . $requestData->deadline_time . ":00"];
        $task->fill($input)->save();
        event(new \App\Events\TaskAction($task, self::UPDATED_DEADLINE));
    }

 
    /**
     * Statistics for Dashboard
     */

    public function tasks()
    {
        return Task::all()->count();
    }

    /**
     * @return mixed
     */
    public function allCompletedTasks()
    {
        return Task::whereHas('status', function ($query)
        {
            $query->whereTitle('Closed');
        })->count();
    }

    /**
     * @return float|int
     */
    public function percantageCompleted()
    {
        if (!$this->tasks() || !$this->allCompletedTasks()) {
            $totalPercentageTasks = 0;
        } else {
            $totalPercentageTasks = $this->allCompletedTasks() / $this->tasks() * 100;
        }

        return $totalPercentageTasks;
    }

    /**
     * @return mixed
     */
    public function createdTasksMothly()
    {
        return DB::table('tasks')
            ->select(DB::raw('count(*) as month, created_at'))
            ->groupBy(DB::raw('YEAR(created_at), MONTH(created_at)'))
            ->get();
    }

    /**
     * @return mixed
     */
    public function completedTasksMothly()
    {
        return Task::select(DB::raw('count(*) as month, updated_at'))
            ->whereHas('status', function ($query)
            {
                $query->whereTitle('Closed');
            })
            ->groupBy(DB::raw('YEAR(updated_at), MONTH(updated_at)'))
            ->get();
    }

    /**
     * @return mixed
     */
    public function createdTasksToday()
    {
        return Task::whereRaw(
            'date(created_at) = ?',
            [Carbon::now()->format('Y-m-d')]
        )->count();
    }

    /**
     * @return mixed
     */
    public function completedTasksToday()
    {
        return Task::whereRaw(
            'date(updated_at) = ?',
            [Carbon::now()->format('Y-m-d')])
            ->whereHas('status', function ($query)
            {
                $query->whereTitle('Closed');
            })->count();
    }

    /**
     * @return mixed
     */
    public function completedTasksThisMonth()
    {
        return Task::select(DB::raw('count(*) as total, updated_at'))
            ->whereHas('status', function ($query)
            {
                $query->whereTitle('Closed');
            })
            ->whereBetween('updated_at', [Carbon::now()->startOfMonth(), Carbon::now()])
            ->get();
    }

    /**
     * @return mixed
     */
    public function totalTimeSpent()
    {
        return DB::table('invoice_lines')
            ->select(DB::raw('SUM(quantity)'))
            ->get();
    }

    /**
     * @param $external_id
     * @return mixed
     */
    public function totalOpenAndClosedTasks($external_id)
    {
        $user = User::whereExternalId($external_id)->first();

        $groups = $user->tasks()->with('status')->get()->sortBy('status.title')->groupBy('status.title');
        $keys = collect();
        $counts = collect();
        foreach ($groups as $groupKey => $group) {
            $keys->push($groupKey);
            $counts->push(count($group));
        }

        return collect(['keys' => $keys, 'counts' => $counts]);
    }
}
