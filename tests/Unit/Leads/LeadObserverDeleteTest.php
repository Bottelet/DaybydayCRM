<?php

namespace Tests\Unit\Leads;

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
        /* Arrange */

        /* Act */
        $this->lead->delete();

        /* Assert */
        $this->assertSoftDeleted($this->lead);
    }

    #[Test]
    public function it_deletes_lead_soft_deletes_relations()
    {
        /* Arrange */

        /* Act */
        $this->assertNotEmpty($this->lead->comments);
        $this->assertNotEmpty($this->lead->activity);
        $this->assertNotEmpty($this->lead->appointments);

        $this->lead->delete();
        $this->lead->refresh();

        /* Assert */
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
        /* Arrange */
        $leadId = $this->lead->id;

        /* Act */
        $this->lead->forceDelete();
        $this->lead->refresh();

        /* Assert */
        $this->assertDatabaseMissing('leads', [
            'id' => $leadId,
        ]);
    }

    #[Test]
    public function it_force_delete_removes_relations_from_database()
    {
        /* Arrange */
        $commentId     = $this->lead->comments->first()->id;
        $appointmentId = $this->lead->appointments->first()->id;
        $activityId    = $this->lead->activity->first()->id;

        /* Act */
        $this->lead->forceDelete();
        $this->lead->refresh();

        /* Assert */
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
        /* Arrange */
        $offer = Offer::factory()->create([
            'source_id' => $this->lead->id,
        ]);

        /* Act */
        $this->lead->forceDelete();

        /* Assert */
        $this->assertNotNull($offer->refresh());
    }
}
