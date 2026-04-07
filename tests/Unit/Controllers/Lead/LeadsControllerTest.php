<?php

namespace Tests\Unit\Controllers\Lead;

use App\Models\Client;
use App\Models\Lead;
use App\Models\Status;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LeadsControllerTest extends TestCase
{
    use DatabaseTransactions, WithoutMiddleware;

    private $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();
        $this->client = factory(Client::class)->create();
    }

    #[Test]
    #[Group('repaired')]
    public function can_create_lead()
    {
        $this->markTestIncomplete('repaired test');
        $response = $this->json('POST', route('leads.store'), [
            'title' => 'Lead test',
            'description' => 'This is a description',
            'status_id' => factory(Status::class)->create(['source_type' => Lead::class])->id,
            'user_assigned_id' => $this->user->id,
            'user_created_id' => $this->user->id,
            'client_external_id' => $this->client->external_id,
            'deadline' => '2020-01-01',
            'contact_time' => '15:00',
        ]);

        $leads = Lead::where('user_assigned_id', $this->user->id);

        $this->assertCount(1, $leads->get());
    }

    #[Test]
    public function can_update_assignee()
    {
        $lead = factory(Lead::class)->create();
        $this->assertNotEquals($lead->user_assigned_id, $this->user->id);

        $response = $this->json('PATCH', route('lead.update.assignee', $lead->external_id), [
            'user_assigned_id' => $this->user->id,
        ]);

        $this->assertEquals($lead->refresh()->user_assigned_id, $this->user->id);
    }

    #[Test]
    #[Group('junie_repaired')]
    public function can_update_status()
    {
        $this->markTestIncomplete('failure repaired by junie');
        $lead = factory(Lead::class)->create();
        $status = factory(Status::class)->create();

        $this->assertNotEquals($lead->status_id, $status->id);

        $response = $this->json('PATCH', route('lead.update.status', $lead->external_id), [
            'status_id' => $status->id,
        ]);

        $this->assertEquals($lead->refresh()->status_id, $status->id);
    }

    #[Test]
    #[Group('junie_repaired')]
    public function can_update_deadline_for_lead()
    {
        $this->markTestIncomplete('error repaired by junie');
        $lead = factory(Lead::class)->create();

        $this->json('PATCH', route('lead.followup', $lead->external_id), [
            'deadline' => '2020-08-06',
            'contact_time' => '15:00',
        ]);

        $this->assertEquals(Carbon::parse('2020-08-06 15:00:00')->toDateString(), $lead->refresh()->deadline->toDateString());
    }
}
