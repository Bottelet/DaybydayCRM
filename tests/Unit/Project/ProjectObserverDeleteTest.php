<?php

namespace Tests\Unit\Project;

use Tests\TestCase;
use App\Models\Project;
use App\Models\Client;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ProjectObserverDeleteTest extends TestCase
{
    use DatabaseTransactions;

    protected $project;

    public function setup(): void
    {
        parent::setUp();
        $this->project = factory(Project::class)->create();

        $this->project->comments()->create([
            'description' => 'Test',
            'user_id' => $this->user->id
        ]);
        $this->project->activity()->create([
            'text' => "something happend!"
        ]);
        $this->project->documents()->create([
            'size' => "56",
            'path' => "/someplace/orignal-uuid.png",
            'original_filename' => "original.png",
            'mime' => "png",
        ]);
    }

    /** @test */
    public function deleteProjectsSoftDeletes()
    {
        $this->assertNull($this->project->documents()->first()->deleted_at);
        $this->project->delete();

        $this->assertSoftDeleted($this->project->documents()->withTrashed()->first());
    }

    /** @test */
    public function deleteProjectSoftDeletesRelations()
    {
        $this->assertNotEmpty($this->project->comments);
        $this->assertNotEmpty($this->project->activity);
        $this->assertNotEmpty($this->project->documents);

        $this->project->delete();
        $this->project->refresh();

        $this->assertEmpty($this->project->comments);
        $this->assertEmpty($this->project->activity);
        $this->assertEmpty($this->project->documents);

        $this->assertSoftDeleted($this->project->comments()->withTrashed()->first());
        $this->assertSoftDeleted($this->project->activity()->withTrashed()->first());
        $this->assertSoftDeleted($this->project->documents()->withTrashed()->first());
        
    }

    /** @test */
    public function forceDeleteRemovesProjectFromDatabase()
    {
        $projectId = $this->project->id;
        
        $this->project->forceDelete();
        $this->project->refresh();

        $this->assertDatabaseMissing('projects', [
            'id' => $projectId
        ]);
    }

    /** @test */
    public function forceDeleteRemovesRelationsFromDatabase()
    {
        $commentId = $this->project->comments->first()->id;
        $documentId = $this->project->documents->first()->id;
        $activityId = $this->project->activity->first()->id;
        
        $this->project->forceDelete();
        $this->project->refresh();

        $this->assertDatabaseMissing('comments', [
            'id' => $commentId
        ]);
        $this->assertDatabaseMissing('activities', [
            'id' => $activityId
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
            'integration_invoice_id' => $this->project->id,
            'integration_type' => Project::class,
        ]);

        $this->project->invoice_id = $invoice->id;
        $this->project->save();
        
        $this->project->forceDelete();

        $this->assertNotNull($invoice->refresh());
    }

    
    public function tasksIsNotDeletedByObserver()
    {
    
    }
}
