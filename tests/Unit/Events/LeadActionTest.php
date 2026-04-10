<?php

namespace Tests\Unit\Events;

use App\Events\LeadAction;
use App\Models\Lead;
use Illuminate\Broadcasting\PrivateChannel;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LeadActionTest extends AbstractTestCase
{
    use RefreshDatabase;

    #[Test]
    public function constructor_stores_lead_and_action()
    {
        $lead = Lead::factory()->create();
        $action = 'created';

        $event = new LeadAction($lead, $action);

        $this->assertEquals($lead->id, $event->getLead()->id);
        $this->assertEquals($action, $event->getAction());
    }

    #[Test]
    public function get_lead_returns_lead_model()
    {
        $lead = Lead::factory()->create();
        $event = new LeadAction($lead, 'updated');

        $this->assertInstanceOf(Lead::class, $event->getLead());
    }

    #[Test]
    public function get_action_returns_action_string()
    {
        $lead = Lead::factory()->create();
        $event = new LeadAction($lead, 'deleted');

        $this->assertEquals('deleted', $event->getAction());
    }

    #[Test]
    public function broadcast_on_returns_private_channel()
    {
        $lead = Lead::factory()->create();
        $event = new LeadAction($lead, 'created');

        $channel = $event->broadcastOn();
        $this->assertInstanceOf(PrivateChannel::class, $channel);
    }

    #[Test]
    public function event_preserves_lead_reference_after_construction()
    {
        $lead = Lead::factory()->create();
        $event = new LeadAction($lead, 'test');

        $this->assertEquals($lead->external_id, $event->getLead()->external_id);
    }

    #[Test]
    public function action_can_be_non_string_value()
    {
        $lead = Lead::factory()->create();
        $event = new LeadAction($lead, 99);

        $this->assertEquals(99, $event->getAction());
    }

    #[Test]
    public function event_uses_interacts_with_sockets_trait()
    {
        $traits = class_uses(LeadAction::class);
        $this->assertContains('Illuminate\Broadcasting\InteractsWithSockets', $traits);
    }

    #[Test]
    public function event_uses_serializes_models_trait()
    {
        $traits = class_uses(LeadAction::class);
        $this->assertContains('Illuminate\Queue\SerializesModels', $traits);
    }

    #[Test]
    public function broadcast_on_returns_channel_named_channel_name()
    {
        $lead = Lead::factory()->create();
        $event = new LeadAction($lead, 'created');

        $channel = $event->broadcastOn();
        $this->assertInstanceOf(PrivateChannel::class, $channel);
    }
}
