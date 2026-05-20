<?php

namespace Tests\Feature\Leads;

use App\Enums\PermissionName;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Lead;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

#[Group('security')]
#[Group('lead-controller')]
class LeadSecurityTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected $lead;

    protected $unauthorizedUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lead = Lead::factory()->create();

        $this->user = User::factory()->withRole('employee')->create();
        $this->actingAs($this->user);

        $this->unauthorizedUser = User::factory()->withRole('employee')->create();

        $this->withoutMiddleware(VerifyCsrfToken::class);
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
        $response = $this->json('DELETE', '/leads/' . $this->lead->external_id . '/json');

        /* Assert */
        $response->assertStatus(403);
        $this->assertDatabaseHas('leads', ['id' => $this->lead->id, 'deleted_at' => null]);
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
        $response = $this->json('PATCH', route('leads.updateAssign', $this->lead->external_id), [
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
        $response = $this->json('PATCH', route('lead.update.status', $this->lead->external_id), [
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
        $response = $this->json('PATCH', route('lead.update.status', $this->lead->external_id), [
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
        $response = $this->json('PATCH', route('lead.update.status', $this->lead->external_id), [
            'status_id' => 999999,
        ]);

        /* Assert */
        $this->lead->refresh();

        $this->assertEquals($originalStatus, $this->lead->status_id);

        $response->assertRedirect();
        $response->assertSessionHas('flash_message_warning', __('Invalid status for lead'));
    }
}
