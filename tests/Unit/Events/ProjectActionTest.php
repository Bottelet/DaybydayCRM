<?php

namespace Tests\Unit\Events;

use App\Events\ProjectAction;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

class ProjectActionTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Freeze time for deterministic tests
        Carbon::setTestNow('2024-01-15 12:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    # region happy_path

    #[Test]
    public function constructor_stores_project_and_action()
    {
        /** Arrange */
        $project = Project::factory()->create();
        $action = 'created';

        /** Act */
        $event = new ProjectAction($project, $action);

        /** Assert */
        $this->assertEquals($project->id, $event->getProject()->id);
        $this->assertEquals($action, $event->getAction());
    }

    #[Test]
    public function get_project_returns_project_model()
    {
        /** Arrange */
        $project = Project::factory()->create();

        /** Act */
        $event = new ProjectAction($project, 'updated');

        /** Assert */
        $this->assertInstanceOf(Project::class, $event->getProject());
    }

    #[Test]
    public function get_action_returns_action_string()
    {
        /** Arrange */
        $project = Project::factory()->create();

        /** Act */
        $event = new ProjectAction($project, 'deleted');

        /** Assert */
        $this->assertEquals('deleted', $event->getAction());
    }

    #[Test]
    public function broadcast_on_returns_private_channel()
    {
        /** Arrange */
        $project = Project::factory()->create();

        /** Act */
        $event = new ProjectAction($project, 'created');
        $channel = $event->broadcastOn();

        /** Assert */
        $this->assertInstanceOf(PrivateChannel::class, $channel);
    }

    #[Test]
    public function event_preserves_project_reference_after_construction()
    {
        /** Arrange */
        $project = Project::factory()->create();

        /** Act */
        $event = new ProjectAction($project, 'test');

        /** Assert */
        $this->assertEquals($project->external_id, $event->getProject()->external_id);
    }

    #[Test]
    public function event_uses_interacts_with_sockets_trait()
    {
        /** Arrange */
        // No arrangement needed

        /** Act */
        $traits = class_uses(ProjectAction::class);

        /** Assert */
        $this->assertContains('Illuminate\Broadcasting\InteractsWithSockets', $traits);
    }

    #[Test]
    public function event_uses_serializes_models_trait()
    {
        /** Arrange */
        // No arrangement needed

        /** Act */
        $traits = class_uses(ProjectAction::class);

        /** Assert */
        $this->assertContains('Illuminate\Queue\SerializesModels', $traits);
    }

    # endregion

    # region edge_cases

    #[Test]
    public function action_can_be_non_string_value()
    {
        /** Arrange */
        $project = Project::factory()->create();

        /** Act */
        $event = new ProjectAction($project, 100);

        /** Assert */
        $this->assertEquals(100, $event->getAction());
    }

    #[Test]
    public function action_can_be_null()
    {
        /** Arrange */
        $project = Project::factory()->create();

        /** Act */
        $event = new ProjectAction($project, null);

        /** Assert */
        $this->assertNull($event->getAction());
    }

    #[Test]
    public function action_can_be_empty_string()
    {
        /** Arrange */
        $project = Project::factory()->create();

        /** Act */
        $event = new ProjectAction($project, '');

        /** Assert */
        $this->assertEquals('', $event->getAction());
    }

    # endregion
}
