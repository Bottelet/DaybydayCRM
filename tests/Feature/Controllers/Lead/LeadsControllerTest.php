<?php

namespace Tests\Feature\Controllers\Lead;

use App\Enums\PermissionName;
use App\Models\Client;
use App\Models\Lead;
use App\Models\Permission;
use App\Models\Status;
use Carbon\Carbon;
use DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

class LeadsControllerTest extends AbstractTestCase
{
    use RefreshDatabase;

    private $client;

    protected function setUp(): void
    {
        parent::setUp();

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
        /* Arrange */
        $this->client = Client::factory()->create();

        /* Act */
        $response = $this->json('POST', route('leads.store'), [
            'title'              => 'Lead test',
            'description'        => 'This is a description',
            'status_id'          => Status::factory()->create(['source_type' => Lead::class])->id,
            'user_assigned_id'   => $this->user->id,
            'user_created_id'    => $this->user->id,
            'client_external_id' => $this->client->external_id,
            'deadline'           => '2020-01-01',
            'contact_time'       => '15:00',
        ]);

        /* Assert */
        $leads = Lead::where('user_assigned_id', $this->user->id);

        $this->assertCount(1, $leads->get());
    }

    #[Test]
    public function it_can_update_assignee()
    {
        /* Arrange */
        $lead = Lead::factory()->create();
        $this->assertNotEquals($lead->user_assigned_id, $this->user->id);

        /* Act */
        $response = $this->json('PATCH', route('leads.updateAssign', $lead->external_id), [
            'user_assigned_id' => $this->user->id,
        ]);

        /* Assert */
        $this->assertEquals($lead->refresh()->user_assigned_id, $this->user->id);
    }

    #[Test]
    public function it_can_update_status()
    {
        /* Arrange */
        $lead   = Lead::factory()->create();
        $status = Status::factory()->create(['source_type' => Lead::class]);

        $this->assertNotEquals($lead->status_id, $status->id);

        /* Act */
        $response = $this->json('PATCH', route('lead.update.status', $lead->external_id), [
            'status_id' => $status->id,
        ]);

        /* Assert */
        $this->assertEquals($lead->refresh()->status_id, $status->id);
    }

    #[Test]
    public function it_can_update_deadline_for_lead()
    {
        /* Arrange */
        $lead = Lead::factory()->create();

        $permission = Permission::firstOrCreate(['name' => 'lead-update-deadline']);
        $this->user->roles->first()->attachPermission($permission);
        $this->user = $this->user->fresh();
        $this->actingAs($this->user);
        Cache::tags('role_user')->flush();

        /* Act */
        $response = $this->json('PATCH', route('lead.update.deadline', $lead->external_id), [
            'deadline_date' => '2020-08-06',
            'deadline_time' => '00:00',
        ]);

        /* Assert */
        $this->assertEquals(Carbon::parse('2020-08-06')->toDateString(), Carbon::parse($lead->refresh()->deadline)->toDateString());
    }

    #[Test]
    public function it_updates_followup_stores_deadline_as_datetime_string()
    {
        /* Arrange */
        $lead = Lead::factory()->create();

        /* Act */
        $response = $this->json('PATCH', route('lead.followup', $lead->external_id), [
            'deadline'     => '2025-06-15',
            'contact_time' => '10:30',
        ]);

        /* Assert */
        $response->assertStatus(302);

        $storedDeadline = $lead->refresh()->deadline;

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
        /* Arrange */
        $lead = Lead::factory()->create();

        /* Act */
        $this->json('PATCH', route('lead.followup', $lead->external_id), [
            'deadline'     => '2025-12-31',
            'contact_time' => '23:59',
        ]);

        /* Assert */
        $storedDeadline = $lead->refresh()->deadline;
        $parsed         = Carbon::parse($storedDeadline);

        $this->assertEquals('2025-12-31', $parsed->toDateString());
        $this->assertEquals('23:59', $parsed->format('H:i'));
    }

    #[Test]
    public function it_updates_followup_deadline_is_stored_as_parseable_date_in_database()
    {
        /* Arrange */
        $lead = Lead::factory()->create();

        /* Act */
        $this->json('PATCH', route('lead.followup', $lead->external_id), [
            'deadline'     => '2025-03-20',
            'contact_time' => '09:00',
        ]);

        /* Assert */
        $rawDeadline = DB::table('leads')->where('id', $lead->id)->value('deadline');

        $this->assertNotNull($rawDeadline);
        $this->assertStringContainsString('2025-03-20', $rawDeadline);
    }
}
