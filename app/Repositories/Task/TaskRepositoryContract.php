<?php
namespace App\Repositories\Task;

interface TaskRepositoryContract
{

    public function find($id);

    public function getTaskTime($id);
    
    public function create($requestData);

    public function updateStatus($id, $requestData);

    public function updateTime($id, $requestData);

    public function updateAssign($id, $requestData);

    public function invoice($id, $requestData);

    public function tasks();

    public function allCompletedTasks();

    public function percantageCompleted();

    public function createdTasksMothly();

    public function completedTasksMothly();

    public function createdTasksToday();

    public function completedTasksToday();

    public function completedTasksThisMonth();

    public function totalTimeSpent();

    public function totalOpenAndClosedTasks($id);
}
