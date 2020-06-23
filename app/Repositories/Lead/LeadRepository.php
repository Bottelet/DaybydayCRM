<?php
namespace App\Repositories\Lead;

use Ramsey\Uuid\Uuid;
use App\Models\Lead;
use App\Models\User;
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
     * @param $external_id
     * @return mixed
     */
    public function findByExternalId($external_id)
    {
        return Lead::whereExternalId($external_id)->first();
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
                'deadline' => $requestData->deadline . " " . $requestData->contact_time . ":00",
                'external_id' => Uuid::uuid4()->toString()]
        );

        $lead = Lead::create($input);
        $insertedExternalId = $lead->external_id;
        Session()->flash('flash_message', __('Lead successfully added'));

        event(new \App\Events\LeadAction($lead, self::CREATED));

        return $insertedExternalId;
    }

    /**
     * @param $external_id
     * @param $requestData
     */
    public function updateStatus($external_id, $requestData)
    {
        $lead = $this->findByExternalId($external_id);
        $lead->fill($requestData->all())->save();
        event(new \App\Events\LeadAction($lead, self::UPDATED_STATUS));
    }

    /**
     * @param $external_id
     * @param $requestData
     */
    public function updateFollowup($external_id, $requestData)
    {
        $lead = $this->findByExternalId($external_id);
        $input = $requestData->all();
        $input = $requestData =
            ['deadline' => $requestData->deadline . " " . $requestData->contact_time . ":00"];
        $lead->fill($input)->save();
        event(new \App\Events\LeadAction($lead, self::UPDATED_DEADLINE));
    }

    /**
     * @param $external_id
     * @param $requestData
     */
    public function updateAssign($external_id, $requestData)
    {
        $lead = $this->findByExternalId($external_id);
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
        return Lead::whereHas('status', function ($query)
            {
                $query->whereTitle('Closed');
            })->count();
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
        )->whereHas('status', function ($query)
            {
                $query->whereTitle('Closed');
            })->count();
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
        return Lead::select(DB::raw('count(*) as total, updated_at'))
            ->whereHas('status', function ($query)
            {
                $query->whereTitle('Closed');
            })
            ->whereBetween('updated_at', [Carbon::now()->startOfMonth(), Carbon::now()])->get();
    }

    /**
     * @return mixed
     */
    public function createdLeadsMonthly()
    {
        return Lead::select(DB::raw('count(*) as month, updated_at'))
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
    public function completedLeadsMonthly()
    {
        return DB::table('leads')
            ->select(DB::raw('count(*) as month, created_at'))
            ->groupBy(DB::raw('YEAR(created_at), MONTH(created_at)'))
            ->get();
    }

    /**
     * @param $external_id
     * @return mixed
     */
    public function totalOpenAndClosedLeads($external_id)
    {
        $user = User::whereExternalId($external_id)->first();

        $groups = $user->leads()->with('status')->get()->sortBy('status.title')->groupBy('status.title');
        $keys = collect();
        $counts = collect();
        foreach ($groups as $groupKey => $group) {
            $keys->push($groupKey);
            $counts->push(count($group));
        }

        return collect(['keys' => $keys, 'counts' => $counts]);
    }
}
