<?php
namespace App\Repositories\Task;

use App\Models\Task;
use Carbon;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use Illuminate\Support\Facades\DB;
use App\Models\Integration;
use App\Models\Activity;
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
    public function getAssignedClient($id)
    {
        $task = Task::findOrFail($id);
        $task->client;
        return $task;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getInvoiceLines($id)
    {
        if (Task::findOrFail($id)->invoice) {
            return Task::findOrFail($id)->invoice->invoiceLines;
        } else {
            return [];
        }
        
    }

    /**
     * @param $requestData
     * @return mixed
     */
    public function create($requestData)
    {
        $client_id = $requestData->get('client_id');

        $input = $requestData = array_merge(
            $requestData->all(),
            ['user_created_id' => auth()->id(),]
        );

        $task = Task::create($input);

        $insertedId = $task->id;
        Session()->flash('flash_message', 'Task successfully added!');
        event(new \App\Events\TaskAction($task, self::CREATED));

        return $insertedId;
    }

    /**
     * @param $id
     * @param $requestData
     */
    public function updateStatus($id, $requestData)
    {
        $task = Task::findOrFail($id);
        $input = $requestData->get('status');
        $input = array_replace($requestData->all(), ['status' => 2]);
        $task->fill($input)->save();
        event(new \App\Events\TaskAction($task, self::UPDATED_STATUS));
    }

    /**
     * @param $id
     * @param $request
     */
    public function updateTime($id, $request)
    {
        $task = Task::findOrFail($id);

        $invoice = $task->invoice;
        if(!$invoice) {
            $invoice = Invoice::create([
                'status' => 'draft',
                'client_id' => $task->client->id
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
     * @param $id
     * @param $requestData
     */
    public function updateAssign($id, $requestData)
    {
        $task = Task::with('user')->findOrFail($id);

        $input = $requestData->get('user_assigned_id');

        $input = array_replace($requestData->all());
        $task->fill($input)->save();
        $task = $task->fresh();

        event(new \App\Events\TaskAction($task, self::UPDATED_ASSIGN));
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
        return Task::where('status', 2)->count();
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
        return DB::table('tasks')
            ->select(DB::raw('count(*) as month, updated_at'))
            ->where('status', 2)
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
            [Carbon::now()->format('Y-m-d')]
        )->where('status', 2)->count();
    }

    /**
     * @return mixed
     */
    public function completedTasksThisMonth()
    {
        return DB::table('tasks')
            ->select(DB::raw('count(*) as total, updated_at'))
            ->where('status', 2)
            ->whereBetween('updated_at', [Carbon::now()->startOfMonth(), Carbon::now()])->get();
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
     * @param $id
     * @return mixed
     */
    public function totalOpenAndClosedTasks($id)
    {
        $open_tasks = Task::where('status', 1)
        ->where('user_assigned_id', $id)
        ->count();

        $closed_tasks = Task::where('status', 2)
        ->where('user_assigned_id', $id)->count();

        return collect([$closed_tasks, $open_tasks]);
    }
}
