<?php

namespace Tests\Unit\Client;

use App\Events\ClientAction;
use App\Models\Client;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

class UpdateAssigneeTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->client = Client::factory()->create([

            'company_name' => 'Just something',
        ]);
    }

    #[Test]
    public function can_update_assignee()
    {
        Event::fake([ClientAction::class]);

        $this->assertNotEquals($this->client->user_id, $this->user->id);

        $this->client->updateAssignee($this->user);

        $this->assertEquals($this->client->user_id, $this->user->id);

        Event::assertDispatched(ClientAction::class);
    }

    #[Test]
    public function can_update_assignee_with_out_permissions_as_any_user()
    {
        Event::fake([ClientAction::class]);

        $user = User::factory()->create();
        $this->actingAs($user);

        $this->client->updateAssignee($this->user);
        $this->assertEquals($this->client->user_id, $this->user->id);

        Event::assertDispatched(ClientAction::class);
    }
}
