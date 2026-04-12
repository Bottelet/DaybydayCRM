<?php

namespace Tests\Unit\Client;

use App\Events\ClientAction;
use App\Models\Client;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

class UpdateAssigneeTest extends AbstractTestCase
{
    use RefreshDatabase;

    /** @var Client */
    protected $client;

    /** @var User */
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Freeze time for deterministic tests
        Carbon::setTestNow('2024-01-15 12:00:00');

        $this->user = User::factory()->create();

        $this->client = Client::factory()->create([
            'company_name' => 'Just something',
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    //region happy_path

    #[Test]
    public function can_update_assignee()
    {
        /** Arrange */
        Event::fake([ClientAction::class]);
        $originalUserId = $this->client->user_id;

        /** Act */
        $this->client->updateAssignee($this->user);

        /** Assert */
        $this->assertNotEquals($originalUserId, $this->user->id);
        $this->assertEquals($this->client->user_id, $this->user->id);
        Event::assertDispatched(ClientAction::class);
    }

    #[Test]
    public function can_update_assignee_with_out_permissions_as_any_user()
    {
        /** Arrange */
        Event::fake([ClientAction::class]);
        $actingUser = User::factory()->create();
        $this->actingAs($actingUser);

        /** Act */
        $this->client->updateAssignee($this->user);

        /** Assert */
        $this->assertEquals($this->client->user_id, $this->user->id);
        Event::assertDispatched(ClientAction::class);
    }

    #[Test]
    public function update_assignee_to_different_user()
    {
        /** Arrange */
        Event::fake([ClientAction::class]);
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();
        $this->client->updateAssignee($firstUser);

        /** Act */
        $this->client->updateAssignee($secondUser);

        /** Assert */
        $this->assertEquals($secondUser->id, $this->client->user_id);
        Event::assertDispatched(ClientAction::class, 2);
    }

    //endregion

    //region edge_cases

    #[Test]
    public function update_assignee_to_same_user_triggers_event()
    {
        /** Arrange */
        Event::fake([ClientAction::class]);
        $this->client->updateAssignee($this->user);
        Event::assertDispatched(ClientAction::class, 1);

        /** Act */
        $this->client->updateAssignee($this->user);

        /** Assert */
        $this->assertEquals($this->user->id, $this->client->user_id);
        Event::assertDispatched(ClientAction::class, 2);
    }

    #[Test]
    public function client_without_assignee_can_be_assigned()
    {
        /** Arrange */
        Event::fake([ClientAction::class]);
        $clientWithoutAssignee = Client::factory()->create([
            'user_id' => null,
        ]);

        /** Act */
        $clientWithoutAssignee->updateAssignee($this->user);

        /** Assert */
        $this->assertEquals($this->user->id, $clientWithoutAssignee->user_id);
        Event::assertDispatched(ClientAction::class);
    }

    #[Test]
    public function multiple_clients_can_have_same_assignee()
    {
        /** Arrange */
        Event::fake([ClientAction::class]);
        $client1 = Client::factory()->create();
        $client2 = Client::factory()->create();

        /** Act */
        $client1->updateAssignee($this->user);
        $client2->updateAssignee($this->user);

        /** Assert */
        $this->assertEquals($this->user->id, $client1->user_id);
        $this->assertEquals($this->user->id, $client2->user_id);
        Event::assertDispatched(ClientAction::class, 2);
    }

    //endregion
}
