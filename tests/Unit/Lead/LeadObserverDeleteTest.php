<?php

namespace Tests\Unit\Lead;

use Tests\TestCase;
use App\Models\Lead;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Offer;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class LeadObserverDeleteTest extends TestCase
{
    use DatabaseTransactions;

    protected $lead;

    public function setup(): void
    {
        parent::setUp();
        $this->lead = factory(Lead::class)->create();

        $this->lead->comments()->create([
            'description' => 'Test',
            'user_id' => $this->user->id
        ]);
        $this->lead->activity()->create([
            'text' => "something happend!"
        ]);
        $this->lead->appointments()->create([
            'title' => 'Some appointment',
            'color' => '#FFFFF',
            'user_id' => $this->user->id
        ]);
    }

    /** @test */
    public function deleteLeadsSoftDeletes()
    {
        $this->lead->delete();
        
        $this->assertSoftDeleted($this->lead);
    }

    /** @test */
    public function deleteLeadsoftDeletesRelations()
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

    /** @test */
    public function forceDeleteRemovesLeadFromDatabase()
    {
        $leadId = $this->lead->id;
        
        $this->lead->forceDelete();
        $this->lead->refresh();

        $this->assertDatabaseMissing('leads', [
            'id' => $leadId
        ]);
    }

    /** @test */
    public function forceDeleteRemovesRelationsFromDatabase()
    {
        $commentId = $this->lead->comments->first()->id;
        $appointmentId = $this->lead->appointments->first()->id;
        $activityId = $this->lead->activity->first()->id;
        
        $this->lead->forceDelete();
        $this->lead->refresh();

        $this->assertDatabaseMissing('comments', [
            'id' => $commentId
        ]);
        $this->assertDatabaseMissing('activities', [
            'id' => $activityId
        ]);
        $this->assertDatabaseMissing('appointments', [
            'id' => $appointmentId
        ]);
    }

    /** @test */
    public function offerIsNotDeletedByObserver()
    {
        $offer = factory(Offer::class)->create([
            'source_id' => $this->lead->id,
        ]);

        $this->lead->forceDelete();

        $this->assertNotNull($offer->refresh());
    }
}
