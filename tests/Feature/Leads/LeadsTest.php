<?php

namespace Tests\Feature\Leads;

use App\Enums\OfferStatus;
use App\Enums\PermissionName;
use App\Http\Controllers\LeadsController;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Client;
use App\Models\Lead;
use App\Models\Offer;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use App\Services\Lead\LeadService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\AbstractTestCase;

#[CoversClass(LeadsController::class)]
class LeadsTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected $lead;

    private User $authorizedUser;

    private User $unauthorizedUser;

    private User $newAssignee;

    private User $userWithPermission;

    private User $userWithoutPermission;

    private $client;

    protected function setUp(): void
    {
        parent::setUp();

        // Create the main test user with a unique role so permission assignments
        // don't leak to other users that share the 'employee' role.
        $this->user = User::factory()->withRole('lead-tester')->create();
        $this->actingAs($this->user);
        $this->withPermissions([
            PermissionName::LEAD_CREATE,
            PermissionName::LEAD_ASSIGN,
            PermissionName::LEAD_UPDATE_STATUS,
            PermissionName::LEAD_UPDATE_DEADLINE,
            PermissionName::LEAD_DELETE,
        ]);

        $this->client = Client::factory()->create();

        $this->authorizedUser   = User::factory()->withRole('lead-authorized')->create();
        $this->unauthorizedUser = User::factory()->withRole('lead-unauthorized')->create();
        $this->newAssignee      = User::factory()->create();

        $client = Client::factory()->create();

        $this->lead = Lead::factory()->create([
            'user_assigned_id' => $this->authorizedUser->id,
            'client_id'        => $client->id,
        ]);

        $this->userWithPermission    = User::factory()->create();
        $this->userWithoutPermission = User::factory()->create();

        $this->lead = Lead::factory()->create();

        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    #[Test]
    public function it_can_create_lead()
    {
        /* Arrange */
        $this->client = Client::factory()->create();

        /* Act */
        $response = $this->withoutMiddleware()->post(route('leads.store'), [
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
    public function it_can_update_assignee()
    {
        /* Arrange */
        $lead = Lead::factory()->create();
        $this->assertNotEquals($lead->user_assigned_id, $this->user->id);

        /* Act */
        $response = $this->withoutMiddleware()->patch(route('leads.updateAssign', $lead->external_id), [
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
        $response = $this->withoutMiddleware()->patch(route('lead.update.status', $lead->external_id), [
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
        $response = $this->withoutMiddleware()->patch(route('lead.update.deadline', $lead->external_id), [
            'deadline_date' => '2020-08-06',
            'deadline_time' => '00:00',
        ]);

        /* Assert */
        $this->assertEquals(Carbon::parse('2020-08-06')->toDateString(), Carbon::parse($lead->refresh()->deadline)->toDateString());
    }

    #[Test]
    public function it_lead_update_assign_only_accepts_user_assigned_id_field()
    {
        /* Arrange */
        $user       = User::factory()->create();
        $this->user = $user;
        $this->withPermissions(PermissionName::LEAD_ASSIGN);

        $newUser             = User::factory()->create();
        $originalTitle       = $this->lead->title;
        $originalDescription = $this->lead->description;

        /* Act */
        $response = $this->patch(route('leads.updateAssign', $this->lead->external_id), [
            'user_assigned_id' => $newUser->id,
            'title'            => 'Malicious Title Change',
            'description'      => 'Malicious Description Change',
            'status_id'        => 999,
        ]);

        /* Assert */
        $this->lead->refresh();

        $response->assertStatus(302);
        $this->assertEquals($newUser->id, $this->lead->user_assigned_id);
        $this->assertEquals($originalTitle, $this->lead->title);
        $this->assertEquals($originalDescription, $this->lead->description);
        $this->assertNotEquals(999, $this->lead->status_id);
    }

    #[Test]
    public function it_lead_update_status_only_accepts_status_id_field()
    {
        /* Arrange */
        $user       = User::factory()->create();
        $this->user = $user;
        $this->withPermissions(PermissionName::LEAD_UPDATE_STATUS);

        $newStatus = Status::factory()->create(['source_type' => Lead::class]);
        while ($newStatus->id == $this->lead->status_id) {
            $newStatus = Status::factory()->create(['source_type' => Lead::class]);
        }

        $originalTitle       = $this->lead->title;
        $originalDescription = $this->lead->description;

        /* Act */
        $response = $this->patch(route('lead.update.status', $this->lead->external_id), [
            'status_id'        => $newStatus->id,
            'title'            => 'Malicious Title Change',
            'description'      => 'Malicious Description Change',
            'user_assigned_id' => 999,
        ]);

        /* Assert */
        $this->lead->refresh();

        $response->assertStatus(302);
        $this->assertEquals($newStatus->id, $this->lead->status_id);
        $this->assertEquals($originalTitle, $this->lead->title);
        $this->assertEquals($originalDescription, $this->lead->description);
        $this->assertNotEquals(999, $this->lead->user_assigned_id);
    }

    #[Test]
    public function it_does_not_delete_offers_if_flag_is_not_given_but_remove_reference()
    {
        /* Arrange */
        $lead  = Lead::factory()->create();
        $offer = Offer::create([
            'source_id'   => $lead->id,
            'source_type' => Lead::class,
            'client_id'   => $lead->client_id,
            'status'      => OfferStatus::inProgress()->getStatus(),
        ]);

        /* Act */
        $response = $this->delete(route('leads.destroy', $lead->external_id), [], ['Accept' => 'application/json']);
        $offer->refresh();

        /* Assert */
        $response->assertStatus(200);
        $this->assertSoftDeleted('leads', ['id' => $lead->id]);
        $this->assertNotNull(Offer::find($offer->id));
        $this->assertNull(Offer::find($offer->source_id));
    }

    #[Test]
    public function it_can_delete_lead_if_flag_is_given_and_offers_does_not_exists()
    {
        /* Arrange */
        $lead = Lead::factory()->create();
        $lead->offers()->forceDelete();

        /* Act */
        $response = $this->delete(route('leads.destroy', $lead->external_id), [
            'delete_offers' => 'on',
        ], ['Accept' => 'application/json']);

        /* Assert */
        $response->assertStatus(200);
        $this->assertNotNull($lead->refresh()->deleted_at);
    }

    #[Test]
    public function it_authorized_user_can_delete_lead()
    {
        /* Arrange */
        $this->withPermissions(PermissionName::LEAD_DELETE);

        /* Act */
        $response = $this->delete(route('leads.destroy', $this->lead->external_id));

        /* Assert */
        $response->assertRedirect();
        $this->assertSoftDeleted('leads', ['id' => $this->lead->id]);
    }

    #[Test]
    public function it_user_with_lead_delete_permission_can_delete_lead()
    {
        /* Arrange */
        $this->user = $this->userWithPermission;
        $this->withPermissions(PermissionName::LEAD_DELETE);

        /* Act */
        $response = $this->delete(route('leads.destroy', $this->lead->external_id));

        /* Assert */
        $response->assertStatus(302);
        $this->assertSoftDeleted('leads', ['id' => $this->lead->id]);
    }

    #[Test]
    public function it_user_without_lead_delete_permission_cannot_delete_lead()
    {
        /* Arrange */
        $this->actingAs($this->userWithoutPermission);

        /* Act */
        $response = $this->delete(route('leads.destroy', $this->lead->external_id));

        /* Assert */
        $response->assertStatus(403);
        $this->assertDatabaseHas('leads', ['id' => $this->lead->id, 'deleted_at' => null]);
    }

    #[Test]
    public function it_unauthorized_user_cannot_delete_lead()
    {
        /* Arrange */
        $this->actingAs($this->unauthorizedUser);

        /* Act */
        $response = $this->delete(route('leads.destroy', $this->lead->external_id));

        /* Assert */
        $response->assertStatus(403);
        $this->assertDatabaseHas('leads', ['id' => $this->lead->id, 'deleted_at' => null]);
    }

    #[Test]
    public function it_unauthorized_user_cannot_delete_lead_via_json()
    {
        /* Arrange */
        $this->actingAs($this->unauthorizedUser);

        /* Act */
        $response = $this->delete('/leads/' . $this->lead->external_id . '/json');

        /* Assert */
        $response->assertStatus(403);
        $this->assertDatabaseHas('leads', ['id' => $this->lead->id, 'deleted_at' => null]);
    }

    #[Test]
    public function it_updates_followup_stores_deadline_as_datetime_string()
    {
        /* Arrange */
        $lead = Lead::factory()->create();

        /* Act */
        $response = $this->withoutMiddleware()->patch(route('lead.followup', $lead->external_id), [
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
        $this->withoutMiddleware()->patch(route('lead.followup', $lead->external_id), [
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
        $this->withoutMiddleware()->patch(route('lead.followup', $lead->external_id), [
            'deadline'     => '2025-03-20',
            'contact_time' => '09:00',
        ]);

        /* Assert */
        $rawDeadline = DB::table('leads')->where('id', $lead->id)->value('deadline');

        $this->assertNotNull($rawDeadline);
        $this->assertStringContainsString('2025-03-20', $rawDeadline);
    }

    #[Test]
    public function it_updates_assign_only_accepts_user_assigned_id_field()
    {
        /* Arrange */
        $this->withPermissions(PermissionName::LEAD_ASSIGN);

        $newUser        = User::factory()->create();
        $originalStatus = $this->lead->status_id;
        $originalTitle  = $this->lead->title;

        /* Act */
        $response = $this->patch(route('leads.updateAssign', $this->lead->external_id), [
            'user_assigned_id' => $newUser->id,
            'status_id'        => 999,
            'title'            => 'Hacked Title',
        ]);

        /* Assert */
        $this->lead->refresh();

        $this->assertEquals($newUser->id, $this->lead->user_assigned_id);

        $this->assertEquals($originalStatus, $this->lead->status_id);

        $this->assertEquals($originalTitle, $this->lead->title);
    }

    #[Test]
    public function it_updates_status_only_accepts_status_id_field()
    {
        /* Arrange */
        $this->withPermissions(PermissionName::LEAD_UPDATE_STATUS);

        $newStatus        = Status::factory()->create(['source_type' => Lead::class]);
        $originalAssignee = $this->lead->user_assigned_id;

        /* Act */
        $response = $this->patch(route('lead.update.status', $this->lead->external_id), [
            'status_id'        => $newStatus->id,
            'user_assigned_id' => $this->user->id,
            'title'            => 'Hacked Title',
        ]);

        /* Assert */
        $this->lead->refresh();

        $this->assertEquals($newStatus->id, $this->lead->status_id);

        $this->assertEquals($originalAssignee, $this->lead->user_assigned_id);

        $this->assertNotEquals('Hacked Title', $this->lead->title);
    }

    #[Test]
    public function it_updates_status_rejects_invalid_status_type()
    {
        /* Arrange */
        $this->withPermissions(PermissionName::LEAD_UPDATE_STATUS);

        $taskStatus     = Status::factory()->create(['source_type' => Task::class]);
        $originalStatus = $this->lead->status_id;

        /* Act */
        $response = $this->patch(route('lead.update.status', $this->lead->external_id), [
            'status_id' => $taskStatus->id,
        ]);

        /* Assert */
        $this->lead->refresh();

        $this->assertEquals($originalStatus, $this->lead->status_id);

        $response->assertRedirect();
        $response->assertSessionHas('flash_message_warning', __('Invalid status for lead'));
    }

    #[Test]
    public function it_updates_status_rejects_nonexistent_status_id()
    {
        /* Arrange */
        $this->withPermissions(PermissionName::LEAD_UPDATE_STATUS);

        $originalStatus = $this->lead->status_id;

        /* Act */
        $response = $this->patch(route('lead.update.status', $this->lead->external_id), [
            'status_id' => 999999,
        ]);

        /* Assert */
        $this->lead->refresh();

        $this->assertEquals($originalStatus, $this->lead->status_id);

        $response->assertRedirect();
        $response->assertSessionHas('flash_message_warning', __('Invalid status for lead'));
    }

    #[Test]
    public function it_deletes_lead()
    {
        /* Arrange */
        $lead = Lead::factory()->create();

        /* Act */
        $response = $this->delete(route('leads.destroy', $lead->external_id), [], ['Accept' => 'application/json']);

        /* Assert */
        $response->assertStatus(200);
        $this->assertSoftDeleted('leads', ['id' => $lead->id]);
    }

    #[Test]
    public function it_deletes_offers_if_flag_given()
    {
        /* Arrange */
        $lead  = Lead::factory()->create();
        $offer = Offer::create([
            'source_id'   => $lead->id,
            'source_type' => Lead::class,
            'client_id'   => $lead->client_id,
            'status'      => OfferStatus::inProgress()->getStatus(),
        ]);

        /* Act */
        $response = $this->delete(route('leads.destroy', $lead->external_id), [
            'delete_offers' => 'on',
        ], ['Accept' => 'application/json']);

        /* Assert */
        $response->assertStatus(200);
        $this->assertSoftDeleted('leads', ['id' => $lead->id]);
        $this->assertSoftDeleted('offers', ['id' => $offer->id]);
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
        $response = $this->post(route('leads.store'), $this->validLeadPayload($status->id), ['Accept' => 'application/json']);

        /* Assert */
        $response->assertStatus(500);
        $response->assertJson([
            'message' => __('Lead could not be created. Please try again.'),
        ]);
    }

    #[Test]
    public function it_authorized_user_can_reassign_lead()
    {
        /* Arrange */
        $this->user = $this->authorizedUser;
        $this->withPermissions(PermissionName::LEAD_ASSIGN);
        $this->user = $this->user->fresh();
        $this->assertTrue($this->user->can('can-assign-new-user-to-lead'));

        // Create a lead specifically assigned to authorizedUser for this test
        $lead             = Lead::factory()->create(['user_assigned_id' => $this->authorizedUser->id]);
        $originalAssignee = $lead->user_assigned_id;
        $this->assertEquals($this->user->id, $originalAssignee);

        /* Act */
        $response = $this->actingAs($this->user)
            ->patch(route('leads.updateAssign', $lead->external_id), [
                'user_assigned_id' => $this->newAssignee->id,
            ]);

        /* Assert */
        $response->assertRedirect();
        $response->assertSessionHas('flash_message');
        $this->assertDatabaseHas('leads', [
            'id'               => $lead->id,
            'user_assigned_id' => $this->newAssignee->id,
        ]);
        $this->assertEquals($this->newAssignee->id, $lead->refresh()->user_assigned_id);
    }

    #[Test]
    public function it_unauthorized_user_cannot_reassign_lead()
    {
        /* Arrange */
        $originalAssignee = $this->lead->user_assigned_id;
        \Illuminate\Support\Facades\Cache::tags('role_user')->flush();
        $this->unauthorizedUser = $this->unauthorizedUser->fresh();
        $this->assertFalse($this->unauthorizedUser->can('can-assign-new-user-to-lead'));

        /* Act */
        $response = $this->actingAs($this->unauthorizedUser)
            ->patch(route('leads.updateAssign', $this->lead->external_id), [
                'user_assigned_id' => $this->newAssignee->id,
            ]);

        /* Assert */
        $response->assertRedirect();
        $response->assertSessionHas('flash_message_warning');
        $this->assertDatabaseHas('leads', [
            'id'               => $this->lead->id,
            'user_assigned_id' => $originalAssignee,
        ]);
        $this->assertEquals($originalAssignee, $this->lead->refresh()->user_assigned_id);
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
