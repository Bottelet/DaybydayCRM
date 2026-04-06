<?php

namespace Tests\Unit\Events;

use App\Events\TaskAction;
use App\Models\Task;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TaskActionTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function constructor_stores_task_and_action()
    {
        $task = factory(Task::class)->create();
        $action = 'created';

        $event = new TaskAction($task, $action);

        $this->assertEquals($task->id, $event->getTask()->id);
        $this->assertEquals($action, $event->getAction());
    }

    /** @test */
    public function get_task_returns_task_model()
    {
        $task = factory(Task::class)->create();
        $event = new TaskAction($task, 'updated');

        $this->assertInstanceOf(Task::class, $event->getTask());
    }

    /** @test */
    public function get_action_returns_action_string()
    {
        $task = factory(Task::class)->create();
        $event = new TaskAction($task, 'deleted');

        $this->assertEquals('deleted', $event->getAction());
    }

    /** @test */
    public function broadcast_on_returns_private_channel()
    {
        $task = factory(Task::class)->create();
        $event = new TaskAction($task, 'created');

        $channel = $event->broadcastOn();
        $this->assertInstanceOf(PrivateChannel::class, $channel);
    }

    /** @test */
    public function event_preserves_task_reference_after_construction()
    {
        $task = factory(Task::class)->create();
        $event = new TaskAction($task, 'test');

        $this->assertEquals($task->external_id, $event->getTask()->external_id);
    }
}
