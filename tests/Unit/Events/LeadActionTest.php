<?php

namespace Tests\Unit\Events;

use App\Events\LeadAction;
use App\Models\Lead;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class LeadActionTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function constructorStoresLeadAndAction()
    {
        $lead = factory(Lead::class)->create();
        $action = 'created';

        $event = new LeadAction($lead, $action);

        $this->assertEquals($lead->id, $event->getLead()->id);
        $this->assertEquals($action, $event->getAction());
    }

    /** @test */
    public function getLeadReturnsLeadModel()
    {
        $lead = factory(Lead::class)->create();
        $event = new LeadAction($lead, 'updated');

        $this->assertInstanceOf(Lead::class, $event->getLead());
    }

    /** @test */
    public function getActionReturnsActionString()
    {
        $lead = factory(Lead::class)->create();
        $event = new LeadAction($lead, 'deleted');

        $this->assertEquals('deleted', $event->getAction());
    }

    /** @test */
    public function broadcastOnReturnsPrivateChannel()
    {
        $lead = factory(Lead::class)->create();
        $event = new LeadAction($lead, 'created');

        $channel = $event->broadcastOn();
        $this->assertInstanceOf(PrivateChannel::class, $channel);
    }

    /** @test */
    public function eventPreservesLeadReferenceAfterConstruction()
    {
        $lead = factory(Lead::class)->create();
        $event = new LeadAction($lead, 'test');

        $this->assertEquals($lead->external_id, $event->getLead()->external_id);
    }
}