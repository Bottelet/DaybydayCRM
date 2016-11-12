<?php
namespace App\Repositories\Lead;

use App\Models\Leads;
use Notifynder;
use Carbon;
use App\Models\Activity;
use DB;

class LeadRepository implements LeadRepositoryContract
{

    CONST CREATED = 'created';
    CONST UPDATED_STATUS = 'updated_status';
    CONST UPDATED_DEADLINE = 'updated_deadline';
    CONST UPDATED_ASSIGN = 'updated_assign';

    public function find($id)
    {
        return Leads::findOrFail($id);
    }

    public function create($requestData)
    {
        $fk_client_id = $requestData->get('fk_client_id');
        $input = $requestData = array_merge(
            $requestData->all(),
            ['fk_user_id_created' => \Auth::id(),
             'contact_date' => $requestData->contact_date ." " . $requestData->contact_time . ":00"]
        );

        $lead = Leads::create($input);
        $insertedId = $lead->id;
        Session()->flash('flash_message', 'Lead successfully added!'); 

        event(new \App\Events\LeadAction($lead, self::CREATED));

        return $insertedId;
    }

    public function updateStatus($id, $requestData)
    {
        $lead = Leads::findOrFail($id);

        $input = $requestData->get('status');
        $input = array_replace($requestData->all(), ['status' => 2]);
        $lead->fill($input)->save();
        event(new \App\Events\LeadAction($lead, self::UPDATED_STATUS));
    }

    public function updateFollowup($id, $requestData)
    {
        $lead = Leads::findOrFail($id);
        $input = $requestData->all();
        $input = $requestData =
         [ 'contact_date' => $requestData->contact_date ." " . $requestData->contact_time . ":00"];
        $lead->fill($input)->save();
        event(new \App\Events\LeadAction($lead, self::UPDATED_DEADLINE));
    }

    public function updateAssign($id, $requestData)
    {
        $lead = Leads::findOrFail($id);

        $input = $requestData->get('fk_user_id_assign');
        $input = array_replace($requestData->all());
        $lead->fill($input)->save();
        $insertedName = $lead->assignee->name;

        event(new \App\Events\LeadAction($lead, self::UPDATED_ASSIGN));
    }

    public function allLeads()
    {
        return Leads::all()->count();
    }

    public function allCompletedLeads()
    {
        return Leads::where('status', 2)->count();
    }

    public function percantageCompleted()
    {
        if (!$this->allLeads() || !$this->allCompletedLeads()) {
            $totalPercentageLeads = 0;
        } else {
            $totalPercentageLeads =  $this->allCompletedLeads() / $this->allLeads() * 100;
        }

        return $totalPercentageLeads;
    }

    public function completedLeadsToday()
    {
        return Leads::whereRaw(
            'date(updated_at) = ?',
            [Carbon::now()->format('Y-m-d')]
        )->where('status', 2)->count();
    }

    public function createdLeadsToday()
    {
        return Leads::whereRaw(
            'date(created_at) = ?',
            [Carbon::now()->format('Y-m-d')]
        )->count();
    }

    public function completedLeadsThisMonth()
    {
        return DB::table('leads')
                 ->select(DB::raw('count(*) as total, updated_at'))
                 ->where('status', 2)
                 ->whereBetween('updated_at', array(Carbon::now()->startOfMonth(), Carbon::now()))->get();
    }

    public function createdLeadsMonthly()
    {
        return DB::table('leads')
             ->select(DB::raw('count(*) as month, updated_at'))
             ->where('status', 2)
             ->groupBy(DB::raw('YEAR(updated_at), MONTH(updated_at)'))
             ->get();
    }

    public function completedLeadsMonthly()
    {
        return DB::table('leads')
         ->select(DB::raw('count(*) as month, created_at'))
         ->groupBy(DB::raw('YEAR(created_at), MONTH(created_at)'))
         ->get();
    }
}
