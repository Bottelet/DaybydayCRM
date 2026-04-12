<?php

namespace Tests\Unit\Controllers\Lead;

use App\Models\Client;
use App\Models\Lead;
use App\Models\Permission;
use App\Models\Status;
use App\Enums\PermissionName;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
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
    public function it_can_create_lead()
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
    public function it_can_update_assignee()
    {
        $lead = Lead::factory()->create();
        $this->assertNotEquals($lead->user_assigned_id, $this->user->id);

        $response = $this->json('PATCH', route('leads.updateAssign', $lead->external_id), [
            'user_assigned_id' => $this->user->id,
        ]);

        $this->assertEquals($lead->refresh()->user_assigned_id, $this->user->id);
    }

    #[Test]
    public function it_can_update_status()
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
    public function it_can_update_deadline_for_lead()
    {
        $lead = Lead::factory()->create();

        // Ensure user has permission
        $permission = Permission::firstOrCreate(['name' => 'lead-update-deadline']);
        $this->user->roles->first()->attachPermission($permission);
        $this->user = $this->user->fresh();
        $this->actingAs($this->user);
        Cache::tags('role_user')->flush();

        $response = $this->json('PATCH', route('lead.update.deadline', $lead->external_id), [
            'deadline_date' => '2020-08-06',
            'deadline_time' => '00:00',
        ]);

        $this->assertEquals(Carbon::parse('2020-08-06')->toDateString(), Carbon::parse($lead->refresh()->deadline)->toDateString());
    }

    #[Test]
    public function it_updates_followup_stores_deadline_as_datetime_string()
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
    public function it_updates_followup_stores_deadline_with_correct_time_component()
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
    public function it_updates_followup_deadline_is_stored_as_parseable_date_in_database()
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

    #[Test]
    public function it_updateFollowup_fails_validation_when_contact_time_is_missing()
    {
        // The PR changed updateFollowup() to use $request->validated() instead of
        // $request->contact_time ?: '00:00'. The old code silently defaulted to '00:00'
        // if contact_time was absent. Now the FormRequest's validation (contact_time: required)
        // is enforced, returning 422 when contact_time is not provided.
        $lead = Lead::factory()->create();

        $response = $this->json('PATCH', route('lead.followup', $lead->external_id), [
            'deadline' => '2025-06-15',
            // contact_time intentionally omitted
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['contact_time']);
    }

    #[Test]
    public function it_updateFollowup_fails_validation_when_deadline_is_missing()
    {
        // Both deadline and contact_time are required by UpdateLeadFollowUpRequest.
        // Verify deadline is also enforced.
        $lead = Lead::factory()->create();

        $response = $this->json('PATCH', route('lead.followup', $lead->external_id), [
            'contact_time' => '10:00',
            // deadline intentionally omitted
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['deadline']);
    }

    #[Test]
    public function it_updateDeadline_uses_default_time_when_deadline_time_is_not_provided()
    {
        // The PR changed $request->deadline_time ?: '00:00' to $request->input('deadline_time', '00:00').
        // Both should behave the same: default to '00:00' when deadline_time is absent.
        $lead = Lead::factory()->create();

        $response = $this->json('PATCH', route('lead.update.deadline', $lead->external_id), [
            'deadline_date' => '2025-09-01',
            // deadline_time intentionally omitted - should default to '00:00'
        ]);

        $storedDeadline = $lead->refresh()->deadline;
        $parsed = Carbon::parse($storedDeadline);

        $this->assertEquals('2025-09-01', $parsed->toDateString());
        $this->assertEquals('00:00', $parsed->format('H:i'), 'deadline_time should default to 00:00 when not provided');
    }
}