<?php

namespace Tests\Unit\Leads;

use App\Models\Client;
use App\Models\Lead;
use App\Models\Offer;
use App\Models\Status;
use App\Models\User;
use App\Services\Lead\LeadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

#[CoversClass(LeadService::class)]
class LeadServiceTest extends AbstractTestCase
{
    use RefreshDatabase;

    private LeadService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new LeadService();
    }

    #[Test]
    public function it_covers_lead_service_methods(): void
    {
        $user   = User::factory()->create();
        $client = Client::factory()->create();
        $open   = Status::factory()->create(['source_type' => Lead::class, 'title' => 'Open']);
        $closed = Status::factory()->create(['source_type' => Lead::class, 'title' => 'Closed']);

        $lead = $this->service->create([
            'title'              => 'L1',
            'description'        => 'desc',
            'user_assigned_id'   => $user->id,
            'deadline'           => '2026-01-01',
            'contact_time'       => '12:15',
            'status_id'          => $open->id,
            'client_external_id' => $client->external_id,
        ], $user->id);

        $this->service->assign($lead, $user->id);
        $this->service->updateFollowup($lead, '2026-01-01', '10:30');
        $this->service->updateDeadline($lead, '2026-01-03', '11:00');

        $this->assertTrue($this->service->updateStatus($lead, ['closeLead' => true]));
        $this->assertTrue($this->service->updateStatus($lead, ['openLead' => true]));
        $this->assertTrue($this->service->updateStatus($lead, ['status_id' => $closed->id]));
        $this->assertFalse($this->service->updateStatus($lead, []));

        $offer = Offer::factory()->create(['source_id' => $lead->id, 'source_type' => Lead::class]);

        $this->service->delete($lead, false);

        $this->assertNull($offer->fresh()->source_id);
    }
}
