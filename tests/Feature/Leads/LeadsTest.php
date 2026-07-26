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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
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

    public static function leadUpdateAssignForbiddenFields(): array
    {
        return [
            'title'       => ['title', 'Malicious Title Change'],
            'description' => ['description', 'Malicious Description Change'],
            'status_id'   => ['status_id', 999],
        ];
    }

    public static function leadUpdateStatusForbiddenFields(): array
    {
        return [
            'title'            => ['title', 'Malicious Title Change'],
            'description'      => ['description', 'Malicious Description Change'],
            'user_assigned_id' => ['user_assigned_id', 999],
        ];
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
    public function it_rejects_lead_creation_when_title_is_missing(): void
    {
        /* Arrange */
        $status  = Status::factory()->create(['source_type' => Lead::class]);
        $payload = $this->validLeadPayload($status->id);
        unset($payload['title']);

        /* Act */
        $response = $this->from(route('leads.create'))->post(route('leads.store'), $payload);

        /* Assert */
        $response->assertRedirect(route('leads.create'));
        $response->assertSessionHasErrors(['title']);
        $this->assertDatabaseMissing('leads', ['description' => 'This is a description']);
    }

    #[Test]
    public function it_prevents_user_without_lead_create_permission_from_storing_lead(): void
    {
        /* Arrange */
        $status = Status::factory()->create(['source_type' => Lead::class]);
        $this->actingAs($this->unauthorizedUser);

        /* Act */
        $response = $this->from(route('leads.create'))
            ->post(route('leads.store'), $this->validLeadPayload($status->id));

        /* Assert: StoreLeadRequest::authorize() failing throws AuthorizationException,
         * which the app's exception Handler converts to a flash+redirect-back for
         * non-JSON requests instead of Laravel's generic 403 error page. */
        $response->assertRedirect(route('leads.create'));
        $response->assertSessionHas('flash_message_warning', 'This action is unauthorized.');
        $this->assertDatabaseMissing('leads', ['title' => 'Leads test']);
    }

    #[Test]
    public function it_shows_lead_without_a_deadline_without_erroring(): void
    {
        /* Arrange */
        $lead = Lead::factory()->create(['deadline' => null]);

        /* Act */
        $response = $this->get(route('leads.show', $lead->external_id));

        /* Assert */
        $response->assertStatus(200);
        $response->assertSee($lead->title);
    }

    #[Test]
    public function it_can_update_lead(): void
    {
        /* Arrange */
        $this->withPermissions([PermissionName::LEAD_UPDATE]);
        $lead = Lead::factory()->create(['title' => 'Old title']);

        /* Act */
        $response = $this->withoutMiddleware()->put(route('leads.update', $lead->external_id), [
            'title'       => 'New title',
            'description' => 'New description',
        ]);

        /* Assert */
        $response->assertRedirect(route('leads.show', $lead->external_id));
        $this->assertEquals('New title', $lead->fresh()->title);
    }

    #[Test]
    public function it_can_update_lead_via_json(): void
    {
        /* Arrange */
        $this->withPermissions([PermissionName::LEAD_UPDATE]);
        $lead = Lead::factory()->create(['title' => 'Old title']);

        /* Act */
        $response = $this->withoutMiddleware()->putJson(route('leads.update', $lead->external_id), [
            'title'       => 'New title',
            'description' => 'New description',
        ]);

        /* Assert */
        $response->assertStatus(200);
        $response->assertJson(['lead_external_id' => $lead->external_id]);
        $this->assertEquals('New title', $lead->fresh()->title);
    }

    #[Test]
    public function it_rejects_lead_update_when_title_is_missing(): void
    {
        /* Arrange */
        $this->withPermissions([PermissionName::LEAD_UPDATE]);
        $lead = Lead::factory()->create(['title' => 'Old title']);

        /* Act */
        $response = $this->from(route('leads.show', $lead->external_id))
            ->put(route('leads.update', $lead->external_id), [
                'description' => 'New description',
            ]);

        /* Assert */
        $response->assertRedirect(route('leads.show', $lead->external_id));
        $response->assertSessionHasErrors(['title']);
        $this->assertEquals('Old title', $lead->fresh()->title);
    }

    #[Test]
    public function it_prevents_user_without_lead_update_permission_from_updating_lead(): void
    {
        /* Arrange */
        $this->actingAs($this->unauthorizedUser);
        $lead = Lead::factory()->create(['title' => 'Old title']);

        /* Act */
        $response = $this->from(route('leads.show', $lead->external_id))
            ->put(route('leads.update', $lead->external_id), [
                'title'       => 'New title',
                'description' => 'New description',
            ]);

        /* Assert: UpdateLeadRequest::authorize() failing throws AuthorizationException,
         * which the app's exception Handler converts to a flash+redirect-back for
         * non-JSON requests instead of Laravel's generic 403 error page. */
        $response->assertRedirect(route('leads.show', $lead->external_id));
        $response->assertSessionHas('flash_message_warning', 'This action is unauthorized.');
        $this->assertEquals('Old title', $lead->fresh()->title);
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
    public function it_rejects_lead_assign_update_when_user_assigned_id_is_missing(): void
    {
        /* Arrange */
        $lead = Lead::factory()->create();

        /* Act */
        $response = $this->from(route('leads.show', $lead->external_id))
            ->patch(route('leads.updateAssign', $lead->external_id), []);

        /* Assert */
        $response->assertRedirect(route('leads.show', $lead->external_id));
        $response->assertSessionHasErrors(['user_assigned_id']);
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
    public function it_rejects_lead_status_update_when_close_lead_and_open_lead_are_both_set(): void
    {
        /* Arrange */
        $lead = Lead::factory()->create();

        /* Act */
        $response = $this->from(route('leads.show', $lead->external_id))
            ->patch(route('lead.update.status', $lead->external_id), [
                'closeLead' => true,
                'openLead'  => true,
            ]);

        /* Assert */
        $response->assertRedirect(route('leads.show', $lead->external_id));
        $response->assertSessionHasErrors(['closeLead', 'openLead']);
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
    public function it_rejects_lead_deadline_update_when_deadline_date_has_invalid_format(): void
    {
        /* Arrange */
        $lead = Lead::factory()->create();

        /* Act */
        $response = $this->from(route('leads.show', $lead->external_id))
            ->patch(route('lead.update.deadline', $lead->external_id), [
                'deadline_date' => '06-08-2020',
            ]);

        /* Assert */
        $response->assertRedirect(route('leads.show', $lead->external_id));
        $response->assertSessionHasErrors(['deadline_date']);
    }

    #[Test]
    public function it_prevents_user_without_lead_update_deadline_permission_from_updating_deadline(): void
    {
        /* Arrange */
        $lead             = Lead::factory()->create();
        $originalDeadline = $lead->deadline;
        $this->actingAs($this->unauthorizedUser);

        /* Act */
        $response = $this->from(route('leads.show', $lead->external_id))
            ->patch(route('lead.update.deadline', $lead->external_id), [
                'deadline_date' => '2025-06-15',
            ]);

        /* Assert: UpdateLeadDeadlineRequest::authorize() failing throws AuthorizationException,
         * which the app's exception Handler converts to a flash+redirect-back for
         * non-JSON requests instead of Laravel's generic 403 error page. */
        $response->assertRedirect(route('leads.show', $lead->external_id));
        $response->assertSessionHas('flash_message_warning', 'This action is unauthorized.');
        $this->assertEquals($originalDeadline, $lead->fresh()->deadline);
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
        $this->assertNull($offer->source_id);
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
        $response = $this->from(route('leads.show', $this->lead->external_id))
            ->delete(route('leads.destroy', $this->lead->external_id));

        /* Assert */
        $response->assertRedirect(route('leads.show', $this->lead->external_id));
        $this->assertSoftDeleted('leads', ['id' => $this->lead->id]);
    }

    #[Test]
    public function it_deletes_lead_when_user_has_permission()
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
    public function it_rejects_lead_deletion_when_user_lacks_permission()
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
    public function it_rejects_lead_followup_update_when_contact_time_is_missing(): void
    {
        /* Arrange */
        $lead = Lead::factory()->create();

        /* Act */
        $response = $this->from(route('leads.show', $lead->external_id))
            ->patch(route('lead.followup', $lead->external_id), [
                'deadline' => '2025-06-15',
            ]);

        /* Assert */
        $response->assertRedirect(route('leads.show', $lead->external_id));
        $response->assertSessionHasErrors(['contact_time']);
    }

    #[Test]
    #[DataProvider('leadUpdateAssignForbiddenFields')]
    public function it_updates_assign_only_accepts_user_assigned_id_field(string $forbiddenField, $forbiddenValue)
    {
        /* Arrange */
        $this->withPermissions(PermissionName::LEAD_ASSIGN);

        $newUser       = User::factory()->create();
        $originalValue = $this->lead->{$forbiddenField};

        /* Act */
        $response = $this->patch(route('leads.updateAssign', $this->lead->external_id), [
            'user_assigned_id' => $newUser->id,
            $forbiddenField    => $forbiddenValue,
        ]);

        /* Assert */
        $this->lead->refresh();

        $response->assertStatus(302);
        $this->assertEquals($newUser->id, $this->lead->user_assigned_id);
        $this->assertEquals($originalValue, $this->lead->{$forbiddenField});
    }

    #[Test]
    #[DataProvider('leadUpdateStatusForbiddenFields')]
    public function it_updates_status_only_accepts_status_id_field(string $forbiddenField, $forbiddenValue)
    {
        /* Arrange */
        $this->withPermissions(PermissionName::LEAD_UPDATE_STATUS);

        $newStatus = Status::factory()->create(['source_type' => Lead::class]);
        while ($newStatus->id == $this->lead->status_id) {
            $newStatus = Status::factory()->create(['source_type' => Lead::class]);
        }
        $originalValue = $this->lead->{$forbiddenField};

        /* Act */
        $response = $this->patch(route('lead.update.status', $this->lead->external_id), [
            'status_id'     => $newStatus->id,
            $forbiddenField => $forbiddenValue,
        ]);

        /* Assert */
        $this->lead->refresh();

        $response->assertStatus(302);
        $this->assertEquals($newStatus->id, $this->lead->status_id);
        $this->assertEquals($originalValue, $this->lead->{$forbiddenField});
    }

    #[Test]
    public function it_updates_status_rejects_invalid_status_type()
    {
        /* Arrange */
        $this->withPermissions(PermissionName::LEAD_UPDATE_STATUS);

        $taskStatus     = Status::factory()->create(['source_type' => Task::class]);
        $originalStatus = $this->lead->status_id;

        /* Act */
        $response = $this->from(route('leads.show', $this->lead->external_id))
            ->patch(route('lead.update.status', $this->lead->external_id), [
                'status_id' => $taskStatus->id,
            ]);

        /* Assert */
        $this->lead->refresh();

        $this->assertEquals($originalStatus, $this->lead->status_id);

        $response->assertRedirect(route('leads.show', $this->lead->external_id));
        $response->assertSessionHas('flash_message_warning', __('Invalid status for lead'));
    }

    #[Test]
    public function it_updates_status_rejects_nonexistent_status_id()
    {
        /* Arrange */
        $this->withPermissions(PermissionName::LEAD_UPDATE_STATUS);

        $originalStatus = $this->lead->status_id;

        /* Act */
        $response = $this->from(route('leads.show', $this->lead->external_id))
            ->patch(route('lead.update.status', $this->lead->external_id), [
                'status_id' => 999999,
            ]);

        /* Assert */
        $this->lead->refresh();

        $this->assertEquals($originalStatus, $this->lead->status_id);

        $response->assertRedirect(route('leads.show', $this->lead->external_id));
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
            ->from(route('leads.show', $lead->external_id))
            ->patch(route('leads.updateAssign', $lead->external_id), [
                'user_assigned_id' => $this->newAssignee->id,
            ]);

        /* Assert */
        $response->assertRedirect(route('leads.show', $lead->external_id));
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
            ->from(route('leads.show', $this->lead->external_id))
            ->patch(route('leads.updateAssign', $this->lead->external_id), [
                'user_assigned_id' => $this->newAssignee->id,
            ]);

        /* Assert */
        $response->assertRedirect(route('leads.show', $this->lead->external_id));
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
