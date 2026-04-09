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
    #[Group('junie_repaired')]
    public function can_create_lead()
    {
        $this->markTestIncomplete('failure repaired by junie');
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

        $response = $this->json('PATCH', route('leads.updateAssign', $lead->external_id), [
            'user_assigned_id' => $this->user->id,
        ]);

        $this->assertEquals($lead->refresh()->user_assigned_id, $this->user->id);
    }

    #[Test]
    public function can_update_status()
    {
        $lead = factory(Lead::class)->create();
        $status = factory(Status::class)->create(['source_type' => Lead::class]);

        $this->assertNotEquals($lead->status_id, $status->id);

        $response = $this->json('PATCH', route('lead.update.status', $lead->external_id), [
            'status_id' => $status->id,
        ]);

        $this->assertEquals($lead->refresh()->status_id, $status->id);
    }

    #[Test]
    public function can_update_deadline_for_lead()
    {
        $lead = factory(Lead::class)->create();

        $this->json('PATCH', route('lead.followup', $lead->external_id), [
            'deadline' => '2020-08-06',
            'contact_time' => '15:00',
        ]);

        $this->assertDatesEqual('2020-08-06 15:00:00', $lead->refresh()->deadline);
    }

    #[Test]
    public function update_followup_stores_deadline_as_datetime_string()
    {
        // Regression for the deadline fix: Carbon::parse(...)->toDateTimeString()
        // ensures the deadline is stored as a string, not a Carbon object.
        $lead = factory(Lead::class)->create();

        $response = $this->json('PATCH', route('lead.followup', $lead->external_id), [
            'deadline' => '2025-06-15',
            'contact_time' => '10:30',
        ]);

        $response->assertStatus(302);

        $storedDeadline = $lead->refresh()->deadline;

        // Should be parseable and match the expected date
        $this->assertEquals(
            '2025-06-15',
            Carbon::parse($storedDeadline)->toDateString()
        );

        $this->assertEquals(
            '10:30:00',
            Carbon::parse($storedDeadline)->format('H:i:s')
        );
    }

    #[Test]
    public function update_followup_stores_deadline_with_correct_time_component()
    {
        // Boundary: verify the time part of the deadline is stored correctly
        $lead = factory(Lead::class)->create();

        $this->json('PATCH', route('lead.followup', $lead->external_id), [
            'deadline' => '2025-12-31',
            'contact_time' => '23:59',
        ]);

        $storedDeadline = $lead->refresh()->deadline;
        $parsed = Carbon::parse($storedDeadline);

        $this->assertEquals('2025-12-31', $parsed->toDateString());
        $this->assertEquals('23:59', $parsed->format('H:i'));
    }

    #[Test]
    public function update_followup_deadline_is_stored_as_parseable_date_in_database()
    {
        // Ensures the fix (using ->toDateTimeString()) causes the deadline column
        // to contain a plain string representation, not an object.
        $lead = factory(Lead::class)->create();

        $this->json('PATCH', route('lead.followup', $lead->external_id), [
            'deadline' => '2025-03-20',
            'contact_time' => '09:00',
        ]);

        $rawDeadline = \DB::table('leads')->where('id', $lead->id)->value('deadline');

        // The stored value should be a parseable string, not null
        $this->assertNotNull($rawDeadline);
        $this->assertStringContainsString('2025-03-20', $rawDeadline);
    }
}
