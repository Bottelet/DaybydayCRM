<?php

namespace Tests\Unit\Lead;

use App\Models\Lead;
use App\Models\Offer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

class LeadObserverDeleteTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected $lead;

    protected function setUp(): void
    {
        parent::setUp();
        $this->lead = Lead::factory()->create();

        $this->lead->comments()->create([
            'description' => 'Test',
            'user_id'     => $this->user->id,
        ]);
        $this->lead->activity()->create([
            'text' => 'something happend!',
        ]);
        $this->lead->appointments()->create([
            'title'   => 'Some appointment',
            'color'   => '#FFFFF',
            'user_id' => $this->user->id,
        ]);
    }

    #[Test]
    public function it_deletes_leads_soft_deletes()
    {
        $this->lead->delete();

        $this->assertSoftDeleted($this->lead);
    }

    #[Test]
    public function it_deletes_leadsoft_deletes_relations()
    {
        $this->assertNotEmpty($this->lead->comments);
        $this->assertNotEmpty($this->lead->activity);
        $this->assertNotEmpty($this->lead->appointments);

        $this->lead->delete();
        $this->lead->refresh();

        $this->assertEmpty($this->lead->comments);
        $this->assertEmpty($this->lead->activity);
        $this->assertEmpty($this->lead->appointments);

        $this->assertSoftDeleted($this->lead->comments()->withTrashed()->first());
        $this->assertSoftDeleted($this->lead->activity()->withTrashed()->first());
        $this->assertSoftDeleted($this->lead->appointments()->withTrashed()->first());
    }

    #[Test]
    public function it_force_delete_removes_lead_from_database()
    {
        $leadId = $this->lead->id;

        $this->lead->forceDelete();
        $this->lead->refresh();

        $this->assertDatabaseMissing('leads', [
            'id' => $leadId,
        ]);
    }

    #[Test]
    public function it_force_delete_removes_relations_from_database()
    {
        $commentId     = $this->lead->comments->first()->id;
        $appointmentId = $this->lead->appointments->first()->id;
        $activityId    = $this->lead->activity->first()->id;

        $this->lead->forceDelete();
        $this->lead->refresh();

        $this->assertDatabaseMissing('comments', [
            'id' => $commentId,
        ]);
        $this->assertDatabaseMissing('activities', [
            'id' => $activityId,
        ]);
        $this->assertDatabaseMissing('appointments', [
            'id' => $appointmentId,
        ]);
    }

    #[Test]
    public function it_offer_is_not_deleted_by_observer()
    {
        $offer = Offer::factory()->create([
            'source_id' => $this->lead->id,
        ]);

        $this->lead->forceDelete();

        $this->assertNotNull($offer->refresh());
    }
}
