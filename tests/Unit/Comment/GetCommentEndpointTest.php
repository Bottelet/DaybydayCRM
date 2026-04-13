<?php

namespace Tests\Unit\Comment;

use App\Models\Lead;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

class GetCommentEndpointTest extends AbstractTestCase
{
    use RefreshDatabase;

    /** @var Lead */
    private $lead;

    /** @var Task */
    private $task;

    /** @var Project */
    private $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lead    = Lead::factory()->create();
        $this->task    = Task::factory()->create();
        $this->project = Project::factory()->create();
    }

    # region happy_path

    #[Test]
    public function it_gets_comment_endpoint_returns_correct_urls_for_lead_task_and_project()
    {
        /** Arrange */
        // Already arranged in setUp()

        /** Act */
        $leadEndpoint    = $this->lead->getCreateCommentEndpoint();
        $taskEndpoint    = $this->task->getCreateCommentEndpoint();
        $projectEndpoint = $this->project->getCreateCommentEndpoint();

        /* Assert */
        $this->assertEquals(url('comments/lead/' . $this->lead->external_id), $leadEndpoint);
        $this->assertEquals(url('comments/task/' . $this->task->external_id), $taskEndpoint);
        $this->assertEquals(url('comments/project/' . $this->project->external_id), $projectEndpoint);
    }

    # endregion
}
