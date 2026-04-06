<?php

namespace Tests\Unit\Client;

use App\Events\ClientAction;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UpdateAssigneeTest extends TestCase
{
    use DatabaseTransactions;

    protected $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();

        $this->client = factory(Client::class)->create([

            'company_name' => 'Just something',
        ]);
    }

    /** @test */
    public function can_update_assignee()
    {
        $this->assertNotEquals($this->client->user_id, $this->user->id);

        $this->expectsEvents(ClientAction::class);
        $this->client->updateAssignee($this->user);

        $this->assertEquals($this->client->user_id, $this->user->id);
    }

    /** @test */
    public function can_update_assignee_with_out_permissions_as_any_user()
    {
        $user = factory(User::class)->create();
        $this->setUser($user);

        $this->expectsEvents(ClientAction::class);
        $this->client->updateAssignee($this->user);
        $this->assertEquals($this->client->user_id, $this->user->id);
    }
}
