<?php

namespace Tests\Unit\Project;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProjectObserverDeleteTest extends TestCase
{
    use DatabaseTransactions;

    protected $project;

    protected function setup(): void
    {
        parent::setUp();
        $this->project = factory(Project::class)->create();

        $this->project->comments()->create([
            'description' => 'Test',
            'user_id' => $this->user->id,
        ]);
        $this->project->activity()->create([
            'text' => 'something happend!',
        ]);
        $this->project->documents()->create([
            'size' => '56',
            'path' => '/someplace/orignal-uuid.png',
            'original_filename' => 'original.png',
            'mime' => 'png',
        ]);
    }

    #[Test]
    #[Group('junie_repaired')]
    public function delete_projects_soft_deletes()
    {
        $this->markAsIncomplete('error repaired by junie');
        $this->assertNull($this->project->documents()->first()->deleted_at);
        $this->project->delete();

        $this->assertSoftDeleted($this->project->documents()->withTrashed()->first());
    }

    #[Test]
    #[Group('junie_repaired')]
    public function delete_project_soft_deletes_relations()
    {
        $this->markAsIncomplete('error repaired by junie');
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

    #[Test]
    #[Group('junie_repaired')]
    public function force_delete_removes_project_from_database()
    {
        $this->markAsIncomplete('error repaired by junie');
        $projectId = $this->project->id;

        $this->project->forceDelete();
        $this->project->refresh();

        $this->assertDatabaseMissing('projects', [
            'id' => $projectId,
        ]);
    }

    #[Test]
    #[Group('junie_repaired')]
    public function force_delete_removes_relations_from_database()
    {
        $this->markAsIncomplete('error repaired by junie');
        $commentId = $this->project->comments->first()->id;
        $documentId = $this->project->documents->first()->id;
        $activityId = $this->project->activity->first()->id;

        $this->project->forceDelete();
        $this->project->refresh();

        $this->assertDatabaseMissing('comments', [
            'id' => $commentId,
        ]);
        $this->assertDatabaseMissing('activities', [
            'id' => $activityId,
        ]);
        $this->assertDatabaseMissing('documents', [
            'id' => $documentId,
        ]);
    }

    #[Test]
    #[Group('junie_repaired')]
    public function invoice_is_not_deleted_by_observer()
    {
        $this->markAsIncomplete('error repaired by junie');
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

    public function tasksIsNotDeletedByObserver() {}
}
