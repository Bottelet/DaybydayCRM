<?php

namespace App\Services\Lead;

use App\Models\Client;
use App\Models\Lead;
use App\Models\Status;
use Illuminate\Support\Carbon;

class LeadService
{
    public function create(array $validated, int $userId): Lead
    {
        $clientId = null;
        if ( ! empty($validated['client_external_id'])) {
            $client   = Client::query()->where('external_id', $validated['client_external_id'])->first();
            $clientId = $client ? $client->id : null;
        }

        return Lead::query()->create([
            'title'            => $validated['title'],
            'description'      => clean($validated['description']),
            'user_assigned_id' => $validated['user_assigned_id'],
            'deadline'         => Carbon::parse($this->buildDeadline($validated['deadline'], $validated['contact_time'] ?? null)),
            'status_id'        => $validated['status_id'],
            'user_created_id'  => $userId,
            'client_id'        => $clientId,
        ]);
    }

    public function delete(Lead $lead, bool $deleteOffers): void
    {
        if ($deleteOffers) {
            $lead->offers()->delete();
        } else {
            $lead->offers()->update(['source_id' => null, 'source_type' => null]);
        }

        $lead->delete();
    }

    public function assign(Lead $lead, int $userAssignedId): void
    {
        $lead->user_assigned_id = $userAssignedId;
        $lead->save();
    }

    public function updateFollowup(Lead $lead, string $deadline, string $contactTime): void
    {
        $lead->deadline = Carbon::parse($this->buildDeadline($deadline, $contactTime))->format('Y-m-d H:i:s');
        $lead->save();
    }

    public function updateDeadline(Lead $lead, string $deadlineDate, ?string $deadlineTime): void
    {
        $lead->deadline = Carbon::parse($this->buildDeadline($deadlineDate, $deadlineTime))->format('Y-m-d H:i:s');
        $lead->save();
    }

    public function updateStatus(Lead $lead, array $validated): bool
    {
        if ( ! empty($validated['closeLead'])) {
            return $this->updateStatusByTitle($lead, 'Closed');
        }

        if ( ! empty($validated['openLead'])) {
            return $this->updateStatusByTitle($lead, 'Open');
        }

        if ( ! empty($validated['status_id']) && Status::query()->where('source_type', Lead::class)->where('id', $validated['status_id'])->exists()) {
            $lead->status_id = $validated['status_id'];
            $lead->save();

            return true;
        }

        return false;
    }

    private function updateStatusByTitle(Lead $lead, string $title): bool
    {
        $status = Status::query()->where('source_type', Lead::class)->where('title', $title)->first();
        if ( ! $status) {
            return false;
        }

        $lead->status_id = $status->id;
        $lead->save();

        return true;
    }

    private function buildDeadline(string $date, ?string $time): string
    {
        $normalizedTime = $time ? $time . ':00' : '00:00:00';

        if (mb_strlen($date) > 10) {
            return $date;
        }

        return $date . ' ' . $normalizedTime;
    }
}
