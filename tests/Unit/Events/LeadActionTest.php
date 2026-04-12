<?php

namespace Tests\Unit\Events;

use App\Events\LeadAction;
use App\Models\Lead;
use Carbon\Carbon;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

class LeadActionTest extends AbstractTestCase
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
    public function it_constructor_stores_lead_and_action()
    {
        /** Arrange */
        $lead = Lead::factory()->create();
        $action = 'created';

        /** Act */
        $event = new LeadAction($lead, $action);

        /** Assert */
        $this->assertEquals($lead->id, $event->getLead()->id);
        $this->assertEquals($action, $event->getAction());
    }

    #[Test]
    public function it_gets_lead_returns_lead_model()
    {
        /** Arrange */
        $lead = Lead::factory()->create();

        /** Act */
        $event = new LeadAction($lead, 'updated');

        /** Assert */
        $this->assertInstanceOf(Lead::class, $event->getLead());
    }

    #[Test]
    public function it_gets_action_returns_action_string()
    {
        /** Arrange */
        $lead = Lead::factory()->create();

        /** Act */
        $event = new LeadAction($lead, 'deleted');

        /** Assert */
        $this->assertEquals('deleted', $event->getAction());
    }

    #[Test]
    public function it_broadcasts_on_returns_private_channel()
    {
        /** Arrange */
        $lead = Lead::factory()->create();

        /** Act */
        $event = new LeadAction($lead, 'created');
        $channel = $event->broadcastOn();

        /** Assert */
        $this->assertInstanceOf(PrivateChannel::class, $channel);
    }

    #[Test]
    public function it_event_preserves_lead_reference_after_construction()
    {
        /** Arrange */
        $lead = Lead::factory()->create();

        /** Act */
        $event = new LeadAction($lead, 'test');

        /** Assert */
        $this->assertEquals($lead->external_id, $event->getLead()->external_id);
    }

    #[Test]
    public function it_event_uses_interacts_with_sockets_trait()
    {
        /** Arrange */
        // No arrangement needed

        /** Act */
        $traits = class_uses(LeadAction::class);

        /** Assert */
        $this->assertContains('Illuminate\Broadcasting\InteractsWithSockets', $traits);
    }

    #[Test]
    public function it_event_uses_serializes_models_trait()
    {
        /** Arrange */
        // No arrangement needed

        /** Act */
        $traits = class_uses(LeadAction::class);

        /** Assert */
        $this->assertContains('Illuminate\Queue\SerializesModels', $traits);
    }

    # endregion

    # region edge_cases

    #[Test]
    public function it_action_can_be_non_string_value()
    {
        /** Arrange */
        $lead = Lead::factory()->create();

        /** Act */
        $event = new LeadAction($lead, 99);

        /** Assert */
        $this->assertEquals(99, $event->getAction());
    }

    #[Test]
    public function it_action_can_be_null()
    {
        /** Arrange */
        $lead = Lead::factory()->create();

        /** Act */
        $event = new LeadAction($lead, null);

        /** Assert */
        $this->assertNull($event->getAction());
    }

    #[Test]
    public function it_action_can_be_empty_string()
    {
        /** Arrange */
        $lead = Lead::factory()->create();

        /** Act */
        $event = new LeadAction($lead, '');

        /** Assert */
        $this->assertEquals('', $event->getAction());
    }

    # endregion
}
