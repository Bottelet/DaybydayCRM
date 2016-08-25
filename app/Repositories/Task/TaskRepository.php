<?php
namespace App\Repositories\Task;

use App\Tasks;
use Notifynder;
use Carbon;
use App\Activity;
use App\TaskTime;
use DB;
use App\Integration;

class TaskRepository implements TaskRepositoryContract
{

    public function find($id)
    {
        return Tasks::findOrFail($id);
    }

    public function getAssignedClient($id)
    {
        $tasks = Tasks::findOrFail($id);
        $tasks->clientAssignee;
        return $tasks;
    }

    public function GetTimeForTask($id)
    {
        $taskstime = Tasks::findOrFail($id);
        $taskstime->allTime;
        return $taskstime;
    }

    public function getTaskTime($id)
    {
        return TaskTime::where('fk_task_id', $id)->get();
    }


    public function create($requestData)
    {
        $fk_client_id = $requestData->get('fk_client_id');
        $input = $requestData = array_merge(
            $requestData->all(),
            ['fk_user_id_created' => auth()->id(), ]
        );

        $task = Tasks::create($input);
        $insertedId = $task->id;

        Session()->flash('flash_message', 'Task successfully added!'); //Snippet in Master.blade.php
        Notifynder::category('task.assign')
        ->from(auth()->id())
        ->to($task->fk_user_id_assign)
        ->url(url('tasks', $insertedId))
        ->expire(Carbon::now()->addDays(14))
        ->send();

        $activityinput = array_merge(
             ['text' => 'Task ' . $task->title .
             ' was created by '. $task->taskCreator->name .
             ' and assigned to' . $task->assignee->name,
             'user_id' => Auth()->id(),
             'type' => 'task',
             'type_id' =>  $insertedId]
         );
        
        Activity::create($activityinput);

        return $insertedId;
    }

    public function updateStatus($id, $requestData)
    {
        $task = Tasks::findOrFail($id);
        $input = $requestData->get('status');
        $input = array_replace($requestData->all(), ['status' => 2]);
        $task->fill($input)->save();

        $activityinput = array_merge(
            ['text' => 'Task was completed by '. Auth()->user()->name,
            'user_id' => Auth()->id(),
            'type' => 'task',
            'type_id' =>  $id]
        );
        Activity::create($activityinput);
    }

    public function updateTime($id, $requestData)
    {
        $task = Tasks::findOrFail($id);

        $input = array_replace($requestData->all(), ['fk_task_id'=>"$task->id"]);
        
        TaskTime::create($input);
        $activityinput = array_merge(
            ['text' => Auth()->user()->name.' Inserted a new time for this task',
            'user_id' => Auth()->id(),
            'type' => 'task',
            'type_id' =>  $id]
        );
        Activity::create($activityinput);
    }

    public function updateAssign($id, $requestData)
    {
        $task = Tasks::with('assignee')->findOrFail($id);

        $input = $requestData->get('fk_user_id_assign');

        $input = array_replace($requestData->all());
        $task->fill($input)->save();
        $task = $task->fresh();
        $insertedName = $task->assignee->name;
        

        $activityinput = array_merge(
            ['text' => auth()->user()->name.' assigned task to '. $insertedName,
            'user_id' => auth()->id(),
            'type' => 'task',
            'type_id' =>  $id]
        );
        Activity::create($activityinput);
    }

    public function invoice($id, $requestData)
    {
        $contatGuid = $requestData->invoiceContact;
        
        $taskname = Tasks::find($id);
        $timemanger = TaskTime::where('fk_task_id', $id)->get();
        $sendMail = $requestData->sendMail;
        $productlines = [];

        foreach ($timemanger as $time) {
            $productlines[] = array(
              'Description' => $time->title,
              'Comments' => $time->comment,
              'BaseAmountValue' => $time->value,
              'Quantity' => $time->time,
              'AccountNumber' => 1000,
              'Unit' => 'hours');
        }

        $api = Integration::getApi('billing');

        $results = $api->createInvoice([
            'Currency' => 'DKK',
            'Description' => $taskname->title,
            'contactId' => $contatGuid,
            'ProductLines' => $productlines]);

        if ($sendMail == true) {
            $bookGuid = $booked->Guid;
            $bookTime = $booked->TimeStamp;
            $api->sendInvoice($bookGuid, $bookTime);
        }
    }

    /**
     * Statistics for Dashboard
     */
    
    public function allTasks()
    {
        return Tasks::all()->count();
    }

    public function allCompletedTasks()
    {
        return Tasks::where('status', 2)->count();
    }

    public function percantageCompleted()
    {
        if (!$this->allTasks() || !$this->allCompletedTasks()) {
            $totalPercentageTasks = 0;
        } else {
            $totalPercentageTasks =  $this->allCompletedTasks() / $this->allTasks() * 100;
        }

        return $totalPercentageTasks;
    }

    public function createdTasksMothly()
    {
        return DB::table('tasks')
                 ->select(DB::raw('count(*) as month, created_at'))
                 ->groupBy(DB::raw('YEAR(created_at), MONTH(created_at)'))
                 ->get();
    }

    public function completedTasksMothly()
    {
        return DB::table('tasks')
                 ->select(DB::raw('count(*) as month, updated_at'))
                 ->where('status', 2)
                 ->groupBy(DB::raw('YEAR(updated_at), MONTH(updated_at)'))
                 ->get();
    }

    public function createdTasksToday()
    {
        return Tasks::whereRaw(
            'date(created_at) = ?',
            [Carbon::now()->format('Y-m-d')]
        )->count();
    }

    public function completedTasksToday()
    {
        return Tasks::whereRaw(
            'date(updated_at) = ?',
            [Carbon::now()->format('Y-m-d')]
        )->where('status', 2)->count();
    }

    public function completedTasksThisMonth()
    {
        return DB::table('tasks')
                 ->select(DB::raw('count(*) as total, updated_at'))
                 ->where('status', 2)
                 ->whereBetween('updated_at', array(Carbon::now()->startOfMonth(), Carbon::now()))->get();
    }

    public function totalTimeSpent()
    {
        return DB::table('tasks_time')
         ->select(DB::raw('SUM(time)'))
         ->get();
    }
}
