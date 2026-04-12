<?php

namespace Tests\Unit\Project;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

class ProjectObserverDeleteTest extends AbstractTestCase
{
    use RefreshDatabase;

    /** @var Project */
    protected $project;

    protected function setUp(): void
    {
        parent::setUp();

        // Freeze time for deterministic tests
        Carbon::setTestNow('2024-01-15 12:00:00');

        $this->project = Project::factory()->create();

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

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    // region happy_path

    #[Test]
    public function delete_projects_soft_deletes()
    {
        /** Arrange */
        $document = $this->project->documents()->first();

        /** Act */
        $this->project->delete();

        /** Assert */
        $this->assertSoftDeleted($this->project->documents()->withTrashed()->first());
    }

    #[Test]
    public function delete_project_soft_deletes_relations()
    {
        /** Arrange */
        $this->assertNotEmpty($this->project->comments);
        $this->assertNotEmpty($this->project->activity);
        $this->assertNotEmpty($this->project->documents);

        /** Act */
        $this->project->delete();
        $this->project->refresh();

        /** Assert */
        $this->assertEmpty($this->project->comments);
        $this->assertEmpty($this->project->activity);
        $this->assertEmpty($this->project->documents);

        $this->assertSoftDeleted($this->project->comments()->withTrashed()->first());
        $this->assertSoftDeleted($this->project->activity()->withTrashed()->first());
        $this->assertSoftDeleted($this->project->documents()->withTrashed()->first());
    }

    #[Test]
    public function force_delete_removes_project_from_database()
    {
        /** Arrange */
        $projectId = $this->project->id;

        /** Act */
        $this->project->forceDelete();

        /** Assert */
        $this->assertDatabaseMissing('projects', [
            'id' => $projectId,
        ]);
    }

    #[Test]
    public function force_delete_removes_relations_from_database()
    {
        /** Arrange */
        $commentId = $this->project->comments->first()->id;
        $documentId = $this->project->documents->first()->id;
        $activityId = $this->project->activity->first()->id;

        /** Act */
        $this->project->forceDelete();

        /** Assert */
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
    public function invoice_is_not_deleted_by_observer()
    {
        /** Arrange */
        $invoice = Invoice::factory()->create([
            'status' => 'Test',
            'client_id' => Client::factory()->create()->id,
            'integration_invoice_id' => $this->project->id,
            'integration_type' => Project::class,
        ]);

        $this->project->invoice_id = $invoice->id;
        $this->project->save();

        /** Act */
        $this->project->forceDelete();

        /** Assert */
        $this->assertNotNull($invoice->refresh());
    }

    // endregion

    // region edge_cases

    #[Test]
    public function delete_project_with_no_relations()
    {
        /** Arrange */
        $projectWithoutRelations = Project::factory()->create();

        /** Act */
        $projectWithoutRelations->delete();

        /** Assert */
        $this->assertSoftDeleted($projectWithoutRelations);
    }

    #[Test]
    public function restore_project_restores_relations()
    {
        /** Arrange */
        $this->project->delete();
        $this->project->refresh();

        /** Act */
        $this->project->restore();
        $this->project->refresh();

        /** Assert */
        $this->assertNotEmpty($this->project->comments);
        $this->assertNotEmpty($this->project->activity);
        $this->assertNotEmpty($this->project->documents);

        $this->assertNull($this->project->comments()->first()->deleted_at);
        $this->assertNull($this->project->activity()->first()->deleted_at);
        $this->assertNull($this->project->documents()->first()->deleted_at);
    }

    #[Test]
    public function force_delete_project_with_no_relations()
    {
        /** Arrange */
        $projectWithoutRelations = Project::factory()->create();
        $projectId = $projectWithoutRelations->id;

        /** Act */
        $projectWithoutRelations->forceDelete();

        /** Assert */
        $this->assertDatabaseMissing('projects', [
            'id' => $projectId,
        ]);
    }

    #[Test]
    public function delete_project_with_null_invoice_id()
    {
        /** Arrange */
        $this->project->invoice_id = null;
        $this->project->save();

        /** Act */
        $this->project->delete();

        /** Assert */
        $this->assertSoftDeleted($this->project);
    }

    // endregion
}
