<?php

namespace Tests\Unit\Events;

use App\Events\TaskAction;
use App\Models\Task;
use Illuminate\Broadcasting\PrivateChannel;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaskActionAbstractTest extends AbstractTestCase
{
    use RefreshDatabase;

    #[Test]
    public function constructor_stores_task_and_action()
    {
        $task = Task::factory()->create();
        $action = 'created';

        $event = new TaskAction($task, $action);

        $this->assertEquals($task->id, $event->getTask()->id);
        $this->assertEquals($action, $event->getAction());
    }

    #[Test]
    public function get_task_returns_task_model()
    {
        $task = Task::factory()->create();
        $event = new TaskAction($task, 'updated');

        $this->assertInstanceOf(Task::class, $event->getTask());
    }

    #[Test]
    public function get_action_returns_action_string()
    {
        $task = Task::factory()->create();
        $event = new TaskAction($task, 'deleted');

        $this->assertEquals('deleted', $event->getAction());
    }

    #[Test]
    public function broadcast_on_returns_private_channel()
    {
        $task = Task::factory()->create();
        $event = new TaskAction($task, 'created');

        $channel = $event->broadcastOn();
        $this->assertInstanceOf(PrivateChannel::class, $channel);
    }

    #[Test]
    public function event_preserves_task_reference_after_construction()
    {
        $task = Task::factory()->create();
        $event = new TaskAction($task, 'test');

        $this->assertEquals($task->external_id, $event->getTask()->external_id);
    }
}
