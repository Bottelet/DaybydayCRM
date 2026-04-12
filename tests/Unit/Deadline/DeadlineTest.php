<?php

namespace Tests\Unit\Deadline;

use App\Models\Lead;
use App\Models\Project;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

class DeadlineTest extends AbstractTestCase
{
    use RefreshDatabase;

    /** @var Task */
    private $task;

    /** @var Lead */
    private $lead;

    /** @var Project */
    private $project;

    protected function setUp(): void
    {
        parent::setUp();

        // Freeze time for deterministic tests
        Carbon::setTestNow('2024-01-15 12:00:00');

        $futureDeadline = Carbon::now()->addHours(2);

        // Create an "open" status for tasks and leads
        $openStatus = \App\Models\Status::factory()->create(['title' => 'open']);

        $this->task = Task::factory()->create(
            [
                'deadline' => $futureDeadline,
                'status_id' => $openStatus->id,
            ]
        );
        $this->lead = Lead::factory()->create(
            [
                'deadline' => $futureDeadline,
                'status_id' => $openStatus->id,
            ]
        );
        $this->project = Project::factory()->create(
            [
                'deadline' => $futureDeadline,
            ]
        );
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    //region happy_path

    #[Test]
    public function not_over_deadline()
    {
        /** Arrange */
        // Already arranged in setUp()

        /** Act */
        $leadResult = $this->lead->isOverDeadline();
        $taskResult = $this->task->isOverDeadline();
        $projectResult = $this->project->isOverDeadline();

        /** Assert */
        $this->assertFalse($leadResult);
        $this->assertFalse($taskResult);
        $this->assertFalse($projectResult);
    }

    #[Test]
    public function is_close_to_deadline()
    {
        /** Arrange */
        // Task, Lead, and Project have deadlines in 2 hours (from setUp)

        /** Act */
        $leadResult = $this->lead->isCloseToDeadline();
        $taskResult = $this->task->isCloseToDeadline();
        $projectResult = $this->project->isCloseToDeadline();

        /** Assert */
        $this->assertTrue($leadResult);
        $this->assertTrue($taskResult);
        $this->assertTrue($projectResult);
    }

    #[Test]
    public function get_days_until_deadline()
    {
        /** Arrange */
        $this->task->deadline = Carbon::now()->addDays(3);
        $this->task->save();

        $this->lead->deadline = Carbon::now()->addDays(3);
        $this->lead->save();

        $this->project->deadline = Carbon::now()->addDays(3);
        $this->project->save();

        /** Act */
        $leadDays = $this->lead->days_until_deadline;
        $taskDays = $this->task->days_until_deadline;
        $projectDays = $this->project->days_until_deadline;

        /** Assert */
        $this->assertEquals(3, $leadDays);
        $this->assertEquals(3, $taskDays);
        $this->assertEquals(3, $projectDays);
    }

    //endregion

    //region edge_cases

    #[Test]
    public function over_deadline()
    {
        /** Arrange */
        $this->task->deadline = Carbon::now()->subDay();
        $this->task->save();

        $this->lead->deadline = Carbon::now()->subDay();
        $this->lead->save();

        $this->project->deadline = Carbon::now()->subDay();
        $this->project->save();

        /** Act */
        $leadResult = $this->lead->isOverDeadline();
        $taskResult = $this->task->isOverDeadline();
        $projectResult = $this->project->isOverDeadline();

        /** Assert */
        $this->assertTrue($leadResult);
        $this->assertTrue($taskResult);
        $this->assertTrue($projectResult);
    }

    #[Test]
    public function is_not_close_to_deadline()
    {
        /** Arrange */
        $this->task->deadline = Carbon::now()->addDays(3);
        $this->task->save();

        $this->lead->deadline = Carbon::now()->addDays(3);
        $this->lead->save();

        $this->project->deadline = Carbon::now()->addDays(3);
        $this->project->save();

        /** Act */
        $leadResult = $this->lead->isCloseToDeadline();
        $taskResult = $this->task->isCloseToDeadline();
        $projectResult = $this->project->isCloseToDeadline();

        /** Assert */
        $this->assertFalse($leadResult);
        $this->assertFalse($taskResult);
        $this->assertFalse($projectResult);
    }

    #[Test]
    public function null_deadline_is_not_over()
    {
        /** Arrange */
        $this->task->deadline = null;
        $this->task->save();

        $this->lead->deadline = null;
        $this->lead->save();

        $this->project->deadline = null;
        $this->project->save();

        /** Act */
        $leadResult = $this->lead->isOverDeadline();
        $taskResult = $this->task->isOverDeadline();
        $projectResult = $this->project->isOverDeadline();

        /** Assert */
        $this->assertFalse($leadResult);
        $this->assertFalse($taskResult);
        $this->assertFalse($projectResult);
    }

    #[Test]
    public function null_deadline_is_not_close()
    {
        /** Arrange */
        $this->task->deadline = null;
        $this->task->save();

        $this->lead->deadline = null;
        $this->lead->save();

        $this->project->deadline = null;
        $this->project->save();

        /** Act */
        $leadResult = $this->lead->isCloseToDeadline();
        $taskResult = $this->task->isCloseToDeadline();
        $projectResult = $this->project->isCloseToDeadline();

        /** Assert */
        $this->assertFalse($leadResult);
        $this->assertFalse($taskResult);
        $this->assertFalse($projectResult);
    }

    //endregion
}
