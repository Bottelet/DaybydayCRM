<?php

namespace Tests\Feature\Leads;

use App\Enums\PermissionName;
use App\Http\Controllers\LeadsController;
use App\Models\Client;
use App\Models\Lead;
use App\Models\Permission;
use App\Models\Status;
use App\Services\Lead\LeadService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\AbstractTestCase;

#[CoversClass(LeadsController::class)]
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
        $response = $this->withoutMiddleware()->json('POST', route('leads.store'), [
            'title'              => 'Leads test',
            'description'        => 'This is a description',
            'status_id'          => Status::factory()->create(['source_type' => Lead::class])->id,
            'user_assigned_id'   => $this->user->id,
            'user_created_id'    => $this->user->id,
            'client_external_id' => $this->client->external_id,
            'deadline'           => '2020-01-01',
            'contact_time'       => '15:00',
        ]);

        /* Assert */
        $leads = Lead::query()->where('user_assigned_id', $this->user->id);

        $this->assertCount(1, $leads->get());
    }

    #[Test]
    public function it_returns_web_error_when_lead_creation_throws_exception()
    {
        /* Arrange */
        $this->bindFailingLeadService();
        $status = Status::factory()->create(['source_type' => Lead::class]);

        /* Act */
        $response = $this->from(route('leads.create'))
            ->post(route('leads.store'), $this->validLeadPayload($status->id));

        /* Assert */
        $response->assertRedirect(route('leads.create'));
        $response->assertSessionHasErrors(['lead']);
    }

    #[Test]
    public function it_returns_json_error_when_lead_creation_throws_exception()
    {
        /* Arrange */
        $this->bindFailingLeadService();
        $status = Status::factory()->create(['source_type' => Lead::class]);

        /* Act */
        $response = $this->json('POST', route('leads.store'), $this->validLeadPayload($status->id));

        /* Assert */
        $response->assertStatus(500);
        $response->assertJson([
            'message' => __('Lead could not be created. Please try again.'),
        ]);
    }

    #[Test]
    public function it_can_update_assignee()
    {
        /* Arrange */
        $lead = Lead::factory()->create();
        $this->assertNotEquals($lead->user_assigned_id, $this->user->id);

        /* Act */
        $response = $this->withoutMiddleware()->json('PATCH', route('leads.updateAssign', $lead->external_id), [
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
        $response = $this->withoutMiddleware()->json('PATCH', route('lead.update.status', $lead->external_id), [
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

        $permission = Permission::query()->firstOrCreate(['name' => 'lead-update-deadline']);
        $this->user->roles->first()->attachPermission($permission);
        $this->user = $this->user->fresh();
        $this->actingAs($this->user);
        Cache::tags('role_user')->flush();

        /* Act */
        $response = $this->withoutMiddleware()->json('PATCH', route('lead.update.deadline', $lead->external_id), [
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
        $response = $this->withoutMiddleware()->json('PATCH', route('lead.followup', $lead->external_id), [
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
        $this->withoutMiddleware()->json('PATCH', route('lead.followup', $lead->external_id), [
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
        $this->withoutMiddleware()->json('PATCH', route('lead.followup', $lead->external_id), [
            'deadline'     => '2025-03-20',
            'contact_time' => '09:00',
        ]);

        /* Assert */
        $rawDeadline = DB::table('leads')->where('id', $lead->id)->value('deadline');

        $this->assertNotNull($rawDeadline);
        $this->assertStringContainsString('2025-03-20', $rawDeadline);
    }

    private function bindFailingLeadService(): void
    {
        $this->app->instance(LeadService::class, new class () extends LeadService {
            public function create(array $validated, int $userId): Lead
            {
                throw new RuntimeException('Simulated lead create failure');
            }
        });
    }

    private function validLeadPayload(int $statusId): array
    {
        return [
            'title'              => 'Leads test',
            'description'        => 'This is a description',
            'status_id'          => $statusId,
            'user_assigned_id'   => $this->user->id,
            'user_created_id'    => $this->user->id,
            'client_external_id' => $this->client->external_id,
            'deadline'           => '2020-01-01',
            'contact_time'       => '15:00',
        ];
    }
}
