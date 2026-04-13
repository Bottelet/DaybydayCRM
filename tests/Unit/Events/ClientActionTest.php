<?php

namespace Tests\Unit\Events;

use App\Events\ClientAction;
use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

class ClientActionTest extends AbstractTestCase
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
    public function it_constructor_stores_client_and_action()
    {
        /** Arrange */
        $client = Client::factory()->create();
        $action = 'created';

        /** Act */
        $event = new ClientAction($client, $action);

        /* Assert */
        $this->assertEquals($client->id, $event->getClient()->id);
        $this->assertEquals($action, $event->getAction());
    }

    #[Test]
    public function it_gets_client_returns_client_model()
    {
        /** Arrange */
        $client = Client::factory()->create();

        /** Act */
        $event = new ClientAction($client, 'updated');

        /* Assert */
        $this->assertInstanceOf(Client::class, $event->getClient());
    }

    #[Test]
    public function it_gets_action_returns_action_string()
    {
        /** Arrange */
        $client = Client::factory()->create();

        /** Act */
        $event = new ClientAction($client, 'deleted');

        /* Assert */
        $this->assertEquals('deleted', $event->getAction());
    }

    #[Test]
    public function it_broadcasts_on_returns_private_channel()
    {
        /** Arrange */
        $client = Client::factory()->create();

        /** Act */
        $event   = new ClientAction($client, 'created');
        $channel = $event->broadcastOn();

        /* Assert */
        $this->assertInstanceOf(PrivateChannel::class, $channel);
    }

    #[Test]
    public function it_event_preserves_client_reference_after_construction()
    {
        /** Arrange */
        $client = Client::factory()->create();

        /** Act */
        $event = new ClientAction($client, 'test');

        /* Assert */
        $this->assertEquals($client->external_id, $event->getClient()->external_id);
    }

    #[Test]
    public function it_event_uses_interacts_with_sockets_trait()
    {
        /** Arrange */
        // No arrangement needed

        /** Act */
        $traits = class_uses(ClientAction::class);

        /* Assert */
        $this->assertContains('Illuminate\Broadcasting\InteractsWithSockets', $traits);
    }

    #[Test]
    public function it_event_uses_serializes_models_trait()
    {
        /** Arrange */
        // No arrangement needed

        /** Act */
        $traits = class_uses(ClientAction::class);

        /* Assert */
        $this->assertContains('Illuminate\Queue\SerializesModels', $traits);
    }

    # endregion

    # region edge_cases

    #[Test]
    public function it_action_can_be_non_string_value()
    {
        /** Arrange */
        $client = Client::factory()->create();

        /** Act */
        $event = new ClientAction($client, 42);

        /* Assert */
        $this->assertEquals(42, $event->getAction());
    }

    #[Test]
    public function it_action_can_be_null()
    {
        /** Arrange */
        $client = Client::factory()->create();

        /** Act */
        $event = new ClientAction($client, null);

        /* Assert */
        $this->assertNull($event->getAction());
    }

    #[Test]
    public function it_action_can_be_empty_string()
    {
        /** Arrange */
        $client = Client::factory()->create();

        /** Act */
        $event = new ClientAction($client, '');

        /* Assert */
        $this->assertEquals('', $event->getAction());
    }

    # endregion
}
