<?php
namespace Tests\Unit\Client;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Client;
use App\Models\User;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Testing\Fakes\EventFake;
use Tests\TestCase;

class UpdateAssigneeTest extends TestCase
{
    use DatabaseTransactions;

    protected $client;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();

        $this->client = factory(Client::class)->create([

            'company_name' => 'Just something'
        ]);
    }

    /** @test */
    public function canUpdateAssignee()
    {
        $this->assertNotEquals($this->client->user_id, $this->user->id);

        $this->expectsEvents(\App\Events\ClientAction::class);
        $this->client->updateAssignee($this->user);

        $this->assertEquals($this->client->user_id, $this->user->id);
    }

    /** @test */
    public function canUpdateAssigneeWithOutPermissionsAsAnyUser()
    {
        $user = factory(User::class)->create();
        $this->setUser($user);

        $this->expectsEvents(\App\Events\ClientAction::class);
        $this->client->updateAssignee($this->user);
        $this->assertEquals($this->client->user_id, $this->user->id);
    }
}
