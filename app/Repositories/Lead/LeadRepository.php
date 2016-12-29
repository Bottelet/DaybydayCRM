<?php
namespace App\Repositories\Lead;

use App\Models\Lead;
use Notifynder;
use Carbon;
use DB;

/**
 * Class LeadRepository
 * @package App\Repositories\Lead
 */
class LeadRepository implements LeadRepositoryContract
{
    /**
     *
     */
    const CREATED = 'created';
    /**
     *
     */
    const UPDATED_STATUS = 'updated_status';
    /**
     *
     */
    const UPDATED_DEADLINE = 'updated_deadline';
    /**
     *
     */
    const UPDATED_ASSIGN = 'updated_assign';

    /**
     * @param $id
     * @return mixed
     */
    public function find($id)
    {
        return Lead::findOrFail($id);
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
            ['user_created_id' => \Auth::id(),
                'contact_date' => $requestData->contact_date . " " . $requestData->contact_time . ":00"]
        );

        $lead = Lead::create($input);
        $insertedId = $lead->id;
        Session()->flash('flash_message', 'Lead successfully added!');

        event(new \App\Events\LeadAction($lead, self::CREATED));

        return $insertedId;
    }

    /**
     * @param $id
     * @param $requestData
     */
    public function updateStatus($id, $requestData)
    {
        $lead = Lead::findOrFail($id);

        $input = $requestData->get('status');
        $input = array_replace($requestData->all(), ['status' => 2]);
        $lead->fill($input)->save();
        event(new \App\Events\LeadAction($lead, self::UPDATED_STATUS));
    }

    /**
     * @param $id
     * @param $requestData
     */
    public function updateFollowup($id, $requestData)
    {
        $lead = Lead::findOrFail($id);
        $input = $requestData->all();
        $input = $requestData =
            ['contact_date' => $requestData->contact_date . " " . $requestData->contact_time . ":00"];
        $lead->fill($input)->save();
        event(new \App\Events\LeadAction($lead, self::UPDATED_DEADLINE));
    }

    /**
     * @param $id
     * @param $requestData
     */
    public function updateAssign($id, $requestData)
    {
        $lead = Lead::findOrFail($id);

        $input = $requestData->get('user_assigned_id');
        $input = array_replace($requestData->all());
        $lead->fill($input)->save();
        $insertedName = $lead->user->name;

        event(new \App\Events\LeadAction($lead, self::UPDATED_ASSIGN));
    }

    /**
     * @return int
     */
    public function leads()
    {
        return Lead::all()->count();
    }

    /**
     * @return mixed
     */
    public function allCompletedLeads()
    {
        return Lead::where('status', 2)->count();
    }

    /**
     * @return float|int
     */
    public function percantageCompleted()
    {
        if (!$this->leads() || !$this->allCompletedLeads()) {
            $totalPercentageLeads = 0;
        } else {
            $totalPercentageLeads = $this->allCompletedLeads() / $this->leads() * 100;
        }

        return $totalPercentageLeads;
    }

    /**
     * @return mixed
     */
    public function completedLeadsToday()
    {
        return Lead::whereRaw(
            'date(updated_at) = ?',
            [Carbon::now()->format('Y-m-d')]
        )->where('status', 2)->count();
    }

    /**
     * @return mixed
     */
    public function createdLeadsToday()
    {
        return Lead::whereRaw(
            'date(created_at) = ?',
            [Carbon::now()->format('Y-m-d')]
        )->count();
    }

    /**
     * @return mixed
     */
    public function completedLeadsThisMonth()
    {
        return DB::table('leads')
            ->select(DB::raw('count(*) as total, updated_at'))
            ->where('status', 2)
            ->whereBetween('updated_at', [Carbon::now()->startOfMonth(), Carbon::now()])->get();
    }

    /**
     * @return mixed
     */
    public function createdLeadsMonthly()
    {
        return DB::table('leads')
            ->select(DB::raw('count(*) as month, updated_at'))
            ->where('status', 2)
            ->groupBy(DB::raw('YEAR(updated_at), MONTH(updated_at)'))
            ->get();
    }

    /**
     * @return mixed
     */
    public function completedLeadsMonthly()
    {
        return DB::table('leads')
            ->select(DB::raw('count(*) as month, created_at'))
            ->groupBy(DB::raw('YEAR(created_at), MONTH(created_at)'))
            ->get();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function totalOpenAndClosedLeads($id)
    {
        $open_leads = Lead::where('status', 1)
        ->where('user_assigned_id', $id)
        ->count();

        $closed_leads = Lead::where('status', 2)
        ->where('user_assigned_id', $id)->count();

        return collect([$closed_leads, $open_leads]);
    }
}
