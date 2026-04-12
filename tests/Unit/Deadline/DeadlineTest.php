<?php

namespace Tests\Unit\Deadline;

use App\Models\Lead;
use App\Models\Project;
use App\Models\Task;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

class DeadlineTest extends AbstractTestCase
{
    /** @var Task */
    private $task;

    /** @var Lead */
    private $lead;

    /** @var Project */
    private $project;

    protected function setUp(): void
    {
        parent::setUp();

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

    #[Test]
    public function not_over_deadline()
    {
        $this->assertFalse($this->lead->isOverDeadline());
        $this->assertFalse($this->task->isOverDeadline());
        $this->assertFalse($this->project->isOverDeadline());
    }

    #[Test]
    public function over_deadline()
    {
        $this->task->deadline = Carbon::now()->subDay();
        $this->task->save();
        
        $this->lead->deadline = Carbon::now()->subDay();
        $this->lead->save();
        
        $this->project->deadline = Carbon::now()->subDay();
        $this->project->save();

        $this->assertTrue($this->lead->isOverDeadline());
        $this->assertTrue($this->task->isOverDeadline());
        $this->assertTrue($this->project->isOverDeadline());
    }

    #[Test]
    public function is_not_close_to_deadline()
    {
        $this->task->deadline = Carbon::now()->addDays(3);
        $this->lead->deadline = Carbon::now()->addDays(3);
        $this->project->deadline = Carbon::now()->addDays(3);

        $this->assertFalse($this->lead->isCloseToDeadline());
        $this->assertFalse($this->task->isCloseToDeadline());
        $this->assertFalse($this->project->isCloseToDeadline());
    }

    #[Test]
    public function is_close_to_deadline()
    {
        $this->assertTrue($this->lead->isCloseToDeadline());
        $this->assertTrue($this->task->isCloseToDeadline());
        $this->assertTrue($this->project->isCloseToDeadline());
    }

    #[Test]
    public function get_days_until_deadline()
    {
        $this->task->deadline = Carbon::now()->addDays(3);
        $this->lead->deadline = Carbon::now()->addDays(3);
        $this->project->deadline = Carbon::now()->addDays(3);

        $this->assertEquals(3, $this->lead->days_until_deadline);
        $this->assertEquals(3, $this->task->days_until_deadline);
        $this->assertEquals(3, $this->project->days_until_deadline);
    }
}
