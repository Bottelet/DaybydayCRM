<?php

namespace Tests\Unit\Task;

use App\Models\Task;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaskObserverDeleteTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected $task;

    protected function setUp(): void
    {
        parent::setUp();
        $this->task = Task::factory()->create();

        $this->task->comments()->create([
            'description' => 'Test',
            'user_id' => $this->user->id,
        ]);
        $this->task->activity()->create([
            'text' => 'something happend!',
        ]);
        $this->task->appointments()->create([
            'title' => 'Some appointment',
            'color' => '#FFFFF',
            'user_id' => $this->user->id,
        ]);
        $this->task->documents()->create([
            'size' => '56',
            'path' => '/someplace/orignal-uuid.png',
            'original_filename' => 'original.png',
            'mime' => 'png',
        ]);
    }

    #[Test]
    public function delete_tasks_soft_deletes()
    {
        $this->assertNull($this->task->documents()->first()->deleted_at);
        $this->task->delete();

        $this->assertSoftDeleted($this->task->documents()->withTrashed()->first());
    }

    #[Test]
    public function delete_task_soft_deletes_relations()
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

    #[Test]
    public function force_delete_removes_task_from_database()
    {
        $taskId = $this->task->id;

        $this->task->forceDelete();
        $this->task->refresh();

        $this->assertDatabaseMissing('tasks', [
            'id' => $taskId,
        ]);
    }

    #[Test]
    public function force_delete_removes_relations_from_database()
    {
        $commentId = $this->task->comments->first()->id;
        $appointmentId = $this->task->appointments->first()->id;
        $documentId = $this->task->documents->first()->id;
        $activityId = $this->task->activity->first()->id;

        $this->task->forceDelete();
        $this->task->refresh();

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
}
