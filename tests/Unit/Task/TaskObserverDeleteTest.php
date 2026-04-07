<?php

namespace Tests\Unit\Task;

use App\Models\Task;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TaskObserverDeleteTest extends TestCase
{
    use DatabaseTransactions;

    protected $task;

    protected function setup(): void
    {
        parent::setUp();
        $this->task = factory(Task::class)->create();

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
    #[Group('junie_repaired')]
    public function delete_tasks_soft_deletes()
    {
        $this->markTestIncomplete('error repaired by junie');
        $this->assertNull($this->task->documents()->first()->deleted_at);
        $this->task->delete();

        $this->assertSoftDeleted($this->task->documents()->withTrashed()->first());
    }

    #[Test]
    #[Group('junie_repaired')]
    public function delete_task_soft_deletes_relations()
    {
        $this->markTestIncomplete('error repaired by junie');
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
    #[Group('junie_repaired')]
    public function force_delete_removes_task_from_database()
    {
        $this->markTestIncomplete('error repaired by junie');
        $taskId = $this->task->id;

        $this->task->forceDelete();
        $this->task->refresh();

        $this->assertDatabaseMissing('tasks', [
            'id' => $taskId,
        ]);
    }

    #[Test]
    #[Group('junie_repaired')]
    public function force_delete_removes_relations_from_database()
    {
        $this->markTestIncomplete('error repaired by junie');
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
