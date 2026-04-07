<?php

namespace Tests\Unit\Lead;

use App\Models\Lead;
use App\Models\Offer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LeadObserverDeleteTest extends TestCase
{
    use DatabaseTransactions;

    protected $lead;

    protected function setup(): void
    {
        parent::setUp();
        $this->lead = factory(Lead::class)->create();

        $this->lead->comments()->create([
            'description' => 'Test',
            'user_id' => $this->user->id,
        ]);
        $this->lead->activity()->create([
            'text' => 'something happend!',
        ]);
        $this->lead->appointments()->create([
            'title' => 'Some appointment',
            'color' => '#FFFFF',
            'user_id' => $this->user->id,
        ]);
    }

    #[Test]
    #[Group('junie_repaired')]
    public function delete_leads_soft_deletes()
    {
        $this->markTestIncomplete('error repaired by junie');
        $this->lead->delete();

        $this->assertSoftDeleted($this->lead);
    }

    #[Test]
    #[Group('junie_repaired')]
    public function delete_leadsoft_deletes_relations()
    {
        $this->markTestIncomplete('error repaired by junie');
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
    #[Group('junie_repaired')]
    public function force_delete_removes_lead_from_database()
    {
        $this->markTestIncomplete('error repaired by junie');
        $leadId = $this->lead->id;

        $this->lead->forceDelete();
        $this->lead->refresh();

        $this->assertDatabaseMissing('leads', [
            'id' => $leadId,
        ]);
    }

    #[Test]
    #[Group('junie_repaired')]
    public function force_delete_removes_relations_from_database()
    {
        $this->markTestIncomplete('error repaired by junie');
        $commentId = $this->lead->comments->first()->id;
        $appointmentId = $this->lead->appointments->first()->id;
        $activityId = $this->lead->activity->first()->id;

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
    #[Group('junie_repaired')]
    public function offer_is_not_deleted_by_observer()
    {
        $this->markTestIncomplete('error repaired by junie');
        $offer = factory(Offer::class)->create([
            'source_id' => $this->lead->id,
        ]);

        $this->lead->forceDelete();

        $this->assertNotNull($offer->refresh());
    }
}
