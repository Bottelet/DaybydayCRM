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
    public function constructor_stores_lead_and_action()
    {
        $lead = factory(Lead::class)->create();
        $action = 'created';

        $event = new LeadAction($lead, $action);

        $this->assertEquals($lead->id, $event->getLead()->id);
        $this->assertEquals($action, $event->getAction());
    }

    /** @test */
    public function get_lead_returns_lead_model()
    {
        $lead = factory(Lead::class)->create();
        $event = new LeadAction($lead, 'updated');

        $this->assertInstanceOf(Lead::class, $event->getLead());
    }

    /** @test */
    public function get_action_returns_action_string()
    {
        $lead = factory(Lead::class)->create();
        $event = new LeadAction($lead, 'deleted');

        $this->assertEquals('deleted', $event->getAction());
    }

    /** @test */
    public function broadcast_on_returns_private_channel()
    {
        $lead = factory(Lead::class)->create();
        $event = new LeadAction($lead, 'created');

        $channel = $event->broadcastOn();
        $this->assertInstanceOf(PrivateChannel::class, $channel);
    }

    /** @test */
    public function event_preserves_lead_reference_after_construction()
    {
        $lead = factory(Lead::class)->create();
        $event = new LeadAction($lead, 'test');

        $this->assertEquals($lead->external_id, $event->getLead()->external_id);
    }
}
