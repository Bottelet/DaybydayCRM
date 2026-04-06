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
    public function constructorStoresClientAndAction()
    {
        $client = factory(Client::class)->create();
        $action = 'created';

        $event = new ClientAction($client, $action);

        $this->assertEquals($client->id, $event->getClient()->id);
        $this->assertEquals($action, $event->getAction());
    }

    /** @test */
    public function getClientReturnsClientModel()
    {
        $client = factory(Client::class)->create();
        $event = new ClientAction($client, 'updated');

        $this->assertInstanceOf(Client::class, $event->getClient());
    }

    /** @test */
    public function getActionReturnsActionString()
    {
        $client = factory(Client::class)->create();
        $event = new ClientAction($client, 'deleted');

        $this->assertEquals('deleted', $event->getAction());
    }

    /** @test */
    public function broadcastOnReturnsPrivateChannel()
    {
        $client = factory(Client::class)->create();
        $event = new ClientAction($client, 'created');

        $channel = $event->broadcastOn();
        $this->assertInstanceOf(PrivateChannel::class, $channel);
    }

    /** @test */
    public function eventPreservesClientReferenceAfterConstruction()
    {
        $client = factory(Client::class)->create();
        $event = new ClientAction($client, 'test');

        $this->assertEquals($client->external_id, $event->getClient()->external_id);
    }
}