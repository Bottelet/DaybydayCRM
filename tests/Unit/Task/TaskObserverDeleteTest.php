<?php

namespace Tests\Unit\Task;

use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

class TaskObserverDeleteTest extends AbstractTestCase
{
    use RefreshDatabase;

    /** @var Task */
    protected $task;

    protected function setUp(): void
    {
        parent::setUp();

        // Freeze time for deterministic tests
        Carbon::setTestNow('2024-01-15 12:00:00');

        $this->task = Task::factory()->create();

        $this->task->comments()->create([
            'description' => 'Test',
            'user_id'     => $this->user->id,
        ]);
        $this->task->activity()->create([
            'text' => 'something happend!',
        ]);
        $this->task->appointments()->create([
            'title'   => 'Some appointment',
            'color'   => '#FFFFF',
            'user_id' => $this->user->id,
        ]);
        $this->task->documents()->create([
            'size'              => '56',
            'path'              => '/someplace/orignal-uuid.png',
            'original_filename' => 'original.png',
            'mime'              => 'png',
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    # region happy_path

    #[Test]
    public function it_deletes_tasks_soft_deletes()
    {
        /** Arrange */
        $document = $this->task->documents()->first();

        /* Act */
        $this->task->delete();

        /* Assert */
        $this->assertSoftDeleted($this->task->documents()->withTrashed()->first());
    }

    #[Test]
    public function it_deletes_task_soft_deletes_relations()
    {
        /* Arrange */
        $this->assertNotEmpty($this->task->comments);
        $this->assertNotEmpty($this->task->activity);
        $this->assertNotEmpty($this->task->appointments);
        $this->assertNotEmpty($this->task->documents);

        /* Act */
        $this->task->delete();
        $this->task->refresh();

        /* Assert */
        $this->assertEmpty($this->task->comments);
        $this->assertEmpty($this->task->activity);
        $this->assertEmpty($this->task->appointments);
        $this->assertEmpty($this->task->documents);

        $this->assertSoftDeleted($this->task->comments()->withTrashed()->first());
        $this->assertSoftDeleted($this->task->activity()->withTrashed()->first());
        $this->assertSoftDeleted($this->task->appointments()->withTrashed()->first());
        $this->assertSoftDeleted($this->task->documents()->withTrashed()->first());
    }

    #[Test]
    public function it_force_delete_removes_task_from_database()
    {
        /** Arrange */
        $taskId = $this->task->id;

        /* Act */
        $this->task->forceDelete();

        /* Assert */
        $this->assertDatabaseMissing('tasks', [
            'id' => $taskId,
        ]);
    }

    #[Test]
    public function it_force_delete_removes_relations_from_database()
    {
        /** Arrange */
        $commentId     = $this->task->comments->first()->id;
        $appointmentId = $this->task->appointments->first()->id;
        $documentId    = $this->task->documents->first()->id;
        $activityId    = $this->task->activity->first()->id;

        /* Act */
        $this->task->forceDelete();

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
        $this->assertDatabaseMissing('documents', [
            'id' => $documentId,
        ]);
    }

    # endregion

    # region edge_cases

    #[Test]
    public function it_deletes_task_with_no_relations()
    {
        /** Arrange */
        $taskWithoutRelations = Task::factory()->create();

        /* Act */
        $taskWithoutRelations->delete();

        /* Assert */
        $this->assertSoftDeleted($taskWithoutRelations);
    }

    #[Test]
    public function it_restore_task_restores_relations()
    {
        /* Arrange */
        $this->task->delete();
        $this->task->refresh();

        /* Act */
        $this->task->restore();
        $this->task->refresh();

        /* Assert */
        $this->assertNotEmpty($this->task->comments);
        $this->assertNotEmpty($this->task->activity);
        $this->assertNotEmpty($this->task->appointments);
        $this->assertNotEmpty($this->task->documents);

        $this->assertNull($this->task->comments()->first()->deleted_at);
        $this->assertNull($this->task->activity()->first()->deleted_at);
        $this->assertNull($this->task->appointments()->first()->deleted_at);
        $this->assertNull($this->task->documents()->first()->deleted_at);
    }

    #[Test]
    public function it_force_delete_task_with_no_relations()
    {
        /** Arrange */
        $taskWithoutRelations = Task::factory()->create();
        $taskId               = $taskWithoutRelations->id;

        /* Act */
        $taskWithoutRelations->forceDelete();

        /* Assert */
        $this->assertDatabaseMissing('tasks', [
            'id' => $taskId,
        ]);
    }

    # endregion
}
