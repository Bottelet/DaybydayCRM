<?php

namespace Tests\Unit\Client;

use App\Events\ClientAction;
use App\Models\Client;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
        $this->markTestIncomplete('uses deprecated method');
        $this->assertNotEquals($this->client->user_id, $this->user->id);

        // $this->expectsEvents(ClientAction::class);
        $this->client->updateAssignee($this->user);

        $this->assertEquals($this->client->user_id, $this->user->id);
    }

    #[Test]
    public function can_update_assignee_with_out_permissions_as_any_user()
    {
        $this->markTestIncomplete('uses deprecated method');
        $user = User::factory()->create();
        $this->setUser($user);

        $this->expectsEvents(ClientAction::class);
        $this->client->updateAssignee($this->user);
        $this->assertEquals($this->client->user_id, $this->user->id);
    }
}
