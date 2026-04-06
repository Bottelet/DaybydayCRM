<?php

namespace Tests\Unit\Events;

use App\Events\ProjectAction;
use App\Models\Project;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProjectActionTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function constructorStoresProjectAndAction()
    {
        $project = factory(Project::class)->create();
        $action = 'created';

        $event = new ProjectAction($project, $action);

        $this->assertEquals($project->id, $event->getProject()->id);
        $this->assertEquals($action, $event->getAction());
    }

    /** @test */
    public function getProjectReturnsProjectModel()
    {
        $project = factory(Project::class)->create();
        $event = new ProjectAction($project, 'updated');

        $this->assertInstanceOf(Project::class, $event->getProject());
    }

    /** @test */
    public function getActionReturnsActionString()
    {
        $project = factory(Project::class)->create();
        $event = new ProjectAction($project, 'deleted');

        $this->assertEquals('deleted', $event->getAction());
    }

    /** @test */
    public function broadcastOnReturnsPrivateChannel()
    {
        $project = factory(Project::class)->create();
        $event = new ProjectAction($project, 'created');

        $channel = $event->broadcastOn();
        $this->assertInstanceOf(PrivateChannel::class, $channel);
    }

    /** @test */
    public function eventPreservesProjectReferenceAfterConstruction()
    {
        $project = factory(Project::class)->create();
        $event = new ProjectAction($project, 'test');

        $this->assertEquals($project->external_id, $event->getProject()->external_id);
    }
}