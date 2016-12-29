<?php
namespace App\Repositories\Lead;

interface LeadRepositoryContract
{
    public function find($id);
    
    public function create($requestData);

    public function updateStatus($id, $requestData);

    public function updateFollowup($id, $requestData);

    public function updateAssign($id, $requestData);

    public function leads();

    public function allCompletedLeads();

    public function percantageCompleted();

    public function completedLeadsToday();

    public function createdLeadsToday();

    public function completedLeadsThisMonth();

    public function createdLeadsMonthly();

    public function completedLeadsMonthly();

    public function totalOpenAndClosedLeads($id);
}
