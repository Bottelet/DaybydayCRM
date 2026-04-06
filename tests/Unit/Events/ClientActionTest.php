<?php

namespace Tests\Unit\Events;

use App\Events\ClientAction;
use App\Models\Client;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ClientActionTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function constructor_stores_client_and_action()
    {
        $client = factory(Client::class)->create();
        $action = 'created';

        $event = new ClientAction($client, $action);

        $this->assertEquals($client->id, $event->getClient()->id);
        $this->assertEquals($action, $event->getAction());
    }

    /** @test */
    public function get_client_returns_client_model()
    {
        $client = factory(Client::class)->create();
        $event = new ClientAction($client, 'updated');

        $this->assertInstanceOf(Client::class, $event->getClient());
    }

    /** @test */
    public function get_action_returns_action_string()
    {
        $client = factory(Client::class)->create();
        $event = new ClientAction($client, 'deleted');

        $this->assertEquals('deleted', $event->getAction());
    }

    /** @test */
    public function broadcast_on_returns_private_channel()
    {
        $client = factory(Client::class)->create();
        $event = new ClientAction($client, 'created');

        $channel = $event->broadcastOn();
        $this->assertInstanceOf(PrivateChannel::class, $channel);
    }

    /** @test */
    public function event_preserves_client_reference_after_construction()
    {
        $client = factory(Client::class)->create();
        $event = new ClientAction($client, 'test');

        $this->assertEquals($client->external_id, $event->getClient()->external_id);
    }
}
