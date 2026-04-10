<?php

namespace Tests\Unit\Events;

use App\Events\ProjectAction;
use App\Models\Project;
use Illuminate\Broadcasting\PrivateChannel;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProjectActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function constructor_stores_project_and_action()
    {
        $project = Project::factory()->create();
        $action = 'created';

        $event = new ProjectAction($project, $action);

        $this->assertEquals($project->id, $event->getProject()->id);
        $this->assertEquals($action, $event->getAction());
    }

    #[Test]
    public function get_project_returns_project_model()
    {
        $project = Project::factory()->create();
        $event = new ProjectAction($project, 'updated');

        $this->assertInstanceOf(Project::class, $event->getProject());
    }

    #[Test]
    public function get_action_returns_action_string()
    {
        $project = Project::factory()->create();
        $event = new ProjectAction($project, 'deleted');

        $this->assertEquals('deleted', $event->getAction());
    }

    #[Test]
    public function broadcast_on_returns_private_channel()
    {
        $project = Project::factory()->create();
        $event = new ProjectAction($project, 'created');

        $channel = $event->broadcastOn();
        $this->assertInstanceOf(PrivateChannel::class, $channel);
    }

    #[Test]
    public function event_preserves_project_reference_after_construction()
    {
        $project = Project::factory()->create();
        $event = new ProjectAction($project, 'test');

        $this->assertEquals($project->external_id, $event->getProject()->external_id);
    }
}
