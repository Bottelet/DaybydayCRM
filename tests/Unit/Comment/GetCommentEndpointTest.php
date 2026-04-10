<?php

namespace Tests\Unit\Comment;

use App\Models\Lead;
use App\Models\Project;
use App\Models\Task;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GetCommentEndpointTest extends AbstractTestCase
{
    use RefreshDatabase;

    private $lead;

    private $task;

    private $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lead = Lead::factory()->create();
        $this->task = Task::factory()->create();
        $this->project = Project::factory()->create();
    }

    #[Test]
    public function happy_path()
    {
        $this->assertEquals(url('comments/lead').DIRECTORY_SEPARATOR.$this->lead->external_id, $this->lead->getCreateCommentEndpoint());
        $this->assertEquals(url('comments/task').DIRECTORY_SEPARATOR.$this->task->external_id, $this->task->getCreateCommentEndpoint());
        $this->assertEquals(url('comments/project').DIRECTORY_SEPARATOR.$this->project->external_id, $this->project->getCreateCommentEndpoint());
    }
}
