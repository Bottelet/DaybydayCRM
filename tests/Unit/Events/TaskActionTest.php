<?php

namespace Tests\Unit\Events;

use App\Events\TaskAction;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

class TaskActionTest extends AbstractTestCase
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

    // region happy_path

    #[Test]
    public function constructor_stores_task_and_action()
    {
        /** Arrange */
        $task = Task::factory()->create();
        $action = 'created';

        /** Act */
        $event = new TaskAction($task, $action);

        /** Assert */
        $this->assertEquals($task->id, $event->getTask()->id);
        $this->assertEquals($action, $event->getAction());
    }

    #[Test]
    public function get_task_returns_task_model()
    {
        /** Arrange */
        $task = Task::factory()->create();

        /** Act */
        $event = new TaskAction($task, 'updated');

        /** Assert */
        $this->assertInstanceOf(Task::class, $event->getTask());
    }

    #[Test]
    public function get_action_returns_action_string()
    {
        /** Arrange */
        $task = Task::factory()->create();

        /** Act */
        $event = new TaskAction($task, 'deleted');

        /** Assert */
        $this->assertEquals('deleted', $event->getAction());
    }

    #[Test]
    public function broadcast_on_returns_private_channel()
    {
        /** Arrange */
        $task = Task::factory()->create();

        /** Act */
        $event = new TaskAction($task, 'created');
        $channel = $event->broadcastOn();

        /** Assert */
        $this->assertInstanceOf(PrivateChannel::class, $channel);
    }

    #[Test]
    public function event_preserves_task_reference_after_construction()
    {
        /** Arrange */
        $task = Task::factory()->create();

        /** Act */
        $event = new TaskAction($task, 'test');

        /** Assert */
        $this->assertEquals($task->external_id, $event->getTask()->external_id);
    }

    #[Test]
    public function event_uses_interacts_with_sockets_trait()
    {
        /** Arrange */
        // No arrangement needed

        /** Act */
        $traits = class_uses(TaskAction::class);

        /** Assert */
        $this->assertContains('Illuminate\Broadcasting\InteractsWithSockets', $traits);
    }

    #[Test]
    public function event_uses_serializes_models_trait()
    {
        /** Arrange */
        // No arrangement needed

        /** Act */
        $traits = class_uses(TaskAction::class);

        /** Assert */
        $this->assertContains('Illuminate\Queue\SerializesModels', $traits);
    }

    // endregion

    // region edge_cases

    #[Test]
    public function action_can_be_non_string_value()
    {
        /** Arrange */
        $task = Task::factory()->create();

        /** Act */
        $event = new TaskAction($task, 42);

        /** Assert */
        $this->assertEquals(42, $event->getAction());
    }

    #[Test]
    public function action_can_be_null()
    {
        /** Arrange */
        $task = Task::factory()->create();

        /** Act */
        $event = new TaskAction($task, null);

        /** Assert */
        $this->assertNull($event->getAction());
    }

    #[Test]
    public function action_can_be_empty_string()
    {
        /** Arrange */
        $task = Task::factory()->create();

        /** Act */
        $event = new TaskAction($task, '');

        /** Assert */
        $this->assertEquals('', $event->getAction());
    }

    // endregion
}
