<?php

namespace Tests\Unit\Events;

use App\Events\ClientAction;
use App\Models\Client;
use Illuminate\Broadcasting\PrivateChannel;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClientActionTest extends AbstractTestCase
{
    use RefreshDatabase;

    #[Test]
    public function constructor_stores_client_and_action()
    {
        $client = Client::factory()->create();
        $action = 'created';

        $event = new ClientAction($client, $action);

        $this->assertEquals($client->id, $event->getClient()->id);
        $this->assertEquals($action, $event->getAction());
    }

    #[Test]
    public function get_client_returns_client_model()
    {
        $client = Client::factory()->create();
        $event = new ClientAction($client, 'updated');

        $this->assertInstanceOf(Client::class, $event->getClient());
    }

    #[Test]
    public function get_action_returns_action_string()
    {
        $client = Client::factory()->create();
        $event = new ClientAction($client, 'deleted');

        $this->assertEquals('deleted', $event->getAction());
    }

    #[Test]
    public function broadcast_on_returns_private_channel()
    {
        $client = Client::factory()->create();
        $event = new ClientAction($client, 'created');

        $channel = $event->broadcastOn();
        $this->assertInstanceOf(PrivateChannel::class, $channel);
    }

    #[Test]
    public function event_preserves_client_reference_after_construction()
    {
        $client = Client::factory()->create();
        $event = new ClientAction($client, 'test');

        $this->assertEquals($client->external_id, $event->getClient()->external_id);
    }

    #[Test]
    public function action_can_be_non_string_value()
    {
        $client = Client::factory()->create();
        $event = new ClientAction($client, 42);

        $this->assertEquals(42, $event->getAction());
    }

    #[Test]
    public function event_uses_interacts_with_sockets_trait()
    {
        $traits = class_uses(ClientAction::class);
        $this->assertContains('Illuminate\Broadcasting\InteractsWithSockets', $traits);
    }

    #[Test]
    public function event_uses_serializes_models_trait()
    {
        $traits = class_uses(ClientAction::class);
        $this->assertContains('Illuminate\Queue\SerializesModels', $traits);
    }
}
