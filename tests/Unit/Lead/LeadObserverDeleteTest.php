<?php

namespace Tests\Unit\User;

use Tests\TestCase;
use App\Models\Lead;
use App\Models\Client;
use App\Models\Invoice;
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
    public function deleteTasksSoftDeletes()
    {
        $this->lead->delete();
        
        $this->assertSoftDeleted($this->lead);
    }

    /** @test */
    public function deleteTaskSoftDeletesRelations()
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
    public function forceDeleteRemovesTaskFromDatabase()
    {
        $taskId = $this->lead->id;
        
        $this->lead->forceDelete();
        $this->lead->refresh();

        $this->assertDatabaseMissing('tasks', [
            'id' => $taskId
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
    public function invoiceIsNotDeletedByObserver()
    {
        $invoice = factory(Invoice::class)->create([
            'status' => 'Test',
            'client_id' => factory(Client::class)->create()->id,
            'integration_invoice_id' => $this->lead->id,
            'integration_type' => Task::class,
        ]);

        $this->lead->invoice_id = $invoice->id;
        $this->lead->save();
        
        $this->lead->forceDelete();

        $this->assertNotNull($invoice->refresh());
    }
}
