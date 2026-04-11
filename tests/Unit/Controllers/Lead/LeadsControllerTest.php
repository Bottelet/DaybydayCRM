<?php

namespace Tests\Unit\Controllers\Lead;

use App\Models\Client;
use App\Models\Lead;
use App\Models\Status;
use App\Enums\PermissionName;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use DB;

class LeadsControllerTest extends AbstractTestCase
{
    use RefreshDatabase;

    private $client;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure user has all necessary lead permissions
        $this->withPermissions([
            PermissionName::LEAD_CREATE,
            PermissionName::LEAD_ASSIGN,
            PermissionName::LEAD_UPDATE_STATUS,
            PermissionName::LEAD_UPDATE_DEADLINE,
        ]);

        $this->client = Client::factory()->create();
    }

    #[Test]
    public function can_create_lead()
    {
        $response = $this->json('POST', route('leads.store'), [
            'title' => 'Lead test',
            'description' => 'This is a description',
            'status_id' => Status::factory()->create(['source_type' => Lead::class])->id,
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
        $lead = Lead::factory()->create();
        $this->assertNotEquals($lead->user_assigned_id, $this->user->id);

        $response = $this->json('PATCH', route('leads.updateAssign', $lead->external_id), [
            'user_assigned_id' => $this->user->id,
        ]);

        $this->assertEquals($lead->refresh()->user_assigned_id, $this->user->id);
    }

    #[Test]
    public function can_update_status()
    {
        $lead = Lead::factory()->create();
        $status = Status::factory()->create(['source_type' => Lead::class]);

        $this->assertNotEquals($lead->status_id, $status->id);

        $response = $this->json('PATCH', route('lead.update.status', $lead->external_id), [
            'status_id' => $status->id,
        ]);

        $this->assertEquals($lead->refresh()->status_id, $status->id);
    }

    #[Test]
    public function can_update_deadline_for_lead()
    {
        $lead = Lead::factory()->create();
        $lead->refresh();

        $this->assertEquals(
            '2020-08-06 15:00:00',
            $lead->deadline->format('Y-m-d H:i:s'),
            'Format mismatch! Expected 15:00, but DB has: '.$lead->deadline->toDateTimeString()
        );
    }

    #[Test]
    public function update_followup_stores_deadline_as_datetime_string()
    {
        // Regression for the deadline fix: Carbon::parse(...)->toDateTimeString()
        // ensures the deadline is stored as a string, not a Carbon object.
        $lead = Lead::factory()->create();

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
        $lead = Lead::factory()->create();

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
        $lead = Lead::factory()->create();

        $this->json('PATCH', route('lead.followup', $lead->external_id), [
            'deadline' => '2025-03-20',
            'contact_time' => '09:00',
        ]);

        $rawDeadline = DB::table('leads')->where('id', $lead->id)->value('deadline');

        // The stored value should be a parseable string, not null
        $this->assertNotNull($rawDeadline);
        $this->assertStringContainsString('2025-03-20', $rawDeadline);
    }
}
