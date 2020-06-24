<?php
namespace Tests\Unit\Deadline;

use App\Models\Lead;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class DeadlineTest extends TestCase
{
    /** @var $task Task */
    private $task;
    /** @var $lead Lead  */
    private $lead;
    /** @var $project Project  */
    private $project;

    public function setUp():void
    {
        parent::setUp();

        $this->task = factory(Task::class)->create(
            [
                'deadline' => Carbon::now()->addHour()
            ]
        );
        $this->lead = factory(Lead::class)->create(
            [
                'deadline' => Carbon::now()->addHour()
            ]
        );
        $this->project = factory(Project::class)->create(
            [
                'deadline' => Carbon::now()->addHour()
            ]
        );
    }

    /** @test */
    public function notOverDeadline()
    {
        $this->assertFalse($this->lead->isOverDeadline());
        $this->assertFalse($this->task->isOverDeadline());
        $this->assertFalse($this->project->isOverDeadline());
    }

    /** @test */
    public function overDeadline()
    {
        $this->task->deadline = Carbon::now()->subDay();
        $this->lead->deadline = Carbon::now()->subDay();
        $this->project->deadline = Carbon::now()->subDay();

        $this->assertTrue($this->lead->isOverDeadline());
        $this->assertTrue($this->task->isOverDeadline());
        $this->assertTrue($this->project->isOverDeadline());
    }

    /** @test */
    public function isNotCloseToDeadline()
    {
        $this->task->deadline = Carbon::now()->addDays(3);
        $this->lead->deadline = Carbon::now()->addDays(3);
        $this->project->deadline = Carbon::now()->addDays(3);

        $this->assertFalse($this->lead->isCloseToDeadline());
        $this->assertFalse($this->task->isCloseToDeadline());
        $this->assertFalse($this->project->isCloseToDeadline());
    }

    /** @test */
    public function IsCloseToDeadline()
    {
        $this->assertTrue($this->lead->isCloseToDeadline());
        $this->assertTrue($this->task->isCloseToDeadline());
        $this->assertTrue($this->project->isCloseToDeadline());
    }

    /** @test */
    public function getDaysUntilDeadline()
    {
        $this->task->deadline = Carbon::now()->addDays(3);
        $this->lead->deadline = Carbon::now()->addDays(3);
        $this->project->deadline = Carbon::now()->addDays(3);

        $this->assertEquals($this->lead->days_until_deadline, 3);
        $this->assertEquals($this->task->days_until_deadline, 3);
        $this->assertEquals($this->project->days_until_deadline, 3);
    }
}
