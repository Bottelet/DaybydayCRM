<?php

namespace Tests\Unit\User;

use Tests\TestCase;
use App\Models\Task;
use App\Models\Client;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TaskObserverDeleteTest extends TestCase
{
    use DatabaseTransactions;

    protected $task;

    public function setup(): void
    {
        parent::setUp();
        $this->task = factory(Task::class)->create();

        $this->task->comments()->create([
            'description' => 'Test',
            'user_id' => $this->user->id
        ]);
        $this->task->activity()->create([
            'text' => "something happend!"
        ]);
        $this->task->appointments()->create([
            'title' => 'Some appointment',
            'color' => '#FFFFF',
            'user_id' => $this->user->id
        ]);
        $this->task->documents()->create([
            'size' => "56",
            'path' => "/someplace/orignal-uuid.png",
            'original_filename' => "original.png",
            'mime' => "png",
        ]);
    }

    /** @test */
    public function deleteTasksSoftDeletes()
    {
        $this->assertNull($this->task->documents()->first()->deleted_at);
        $this->task->delete();

        $this->assertSoftDeleted($this->task->documents()->withTrashed()->first());
    }

    /** @test */
    public function deleteTaskSoftDeletesRelations()
    {
        $this->assertNotEmpty($this->task->comments);
        $this->assertNotEmpty($this->task->activity);
        $this->assertNotEmpty($this->task->appointments);
        $this->assertNotEmpty($this->task->documents);

        $this->task->delete();
        $this->task->refresh();

        $this->assertEmpty($this->task->comments);
        $this->assertEmpty($this->task->activity);
        $this->assertEmpty($this->task->appointments);
        $this->assertEmpty($this->task->documents);

        $this->assertSoftDeleted($this->task->comments()->withTrashed()->first());
        $this->assertSoftDeleted($this->task->activity()->withTrashed()->first());
        $this->assertSoftDeleted($this->task->appointments()->withTrashed()->first());
        $this->assertSoftDeleted($this->task->documents()->withTrashed()->first());
        
    }

    /** @test */
    public function forceDeleteRemovesTaskFromDatabase()
    {
        $taskId = $this->task->id;
        
        $this->task->forceDelete();
        $this->task->refresh();

        $this->assertDatabaseMissing('tasks', [
            'id' => $taskId
        ]);
    }

    /** @test */
    public function forceDeleteRemovesRelationsFromDatabase()
    {
        $commentId = $this->task->comments->first()->id;
        $appointmentId = $this->task->appointments->first()->id;
        $documentId = $this->task->documents->first()->id;
        $activityId = $this->task->activity->first()->id;
        
        $this->task->forceDelete();
        $this->task->refresh();

        $this->assertDatabaseMissing('comments', [
            'id' => $commentId
        ]);
        $this->assertDatabaseMissing('activities', [
            'id' => $activityId
        ]);
        $this->assertDatabaseMissing('appointments', [
            'id' => $appointmentId
        ]);
        $this->assertDatabaseMissing('documents', [
            'id' => $documentId
        ]);
    }

    /** @test */
    public function invoiceIsNotDeletedByObserver()
    {
        $invoice = factory(Invoice::class)->create([
            'status' => 'Test',
            'client_id' => factory(Client::class)->create()->id,
            'integration_invoice_id' => $this->task->id,
            'integration_type' => Task::class,
        ]);

        $this->task->invoice_id = $invoice->id;
        $this->task->save();
        
        $this->task->forceDelete();

        $this->assertNotNull($invoice->refresh());
    }
}
