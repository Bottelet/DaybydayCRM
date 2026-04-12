<?php

namespace Tests\Feature\Controllers\Lead;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Lead;
use App\Models\Status;
use App\Models\User;
use App\Enums\PermissionName;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

#[Group('authorization-fix')]
class LeadAuthorizationTest extends AbstractTestCase
{
    use RefreshDatabase;

    private Lead $lead;

    private User $userWithPermission;

    private User $userWithoutPermission;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lead = Lead::factory()->create();

        // Create users
        $this->userWithPermission = User::factory()->create();
        $this->userWithoutPermission = User::factory()->create();

        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    #[Test]
    public function it_user_with_lead_delete_permission_can_delete_lead()
    {
        $this->user = $this->userWithPermission;
        $this->withPermissions(PermissionName::LEAD_DELETE);

        $response = $this->delete(route('leads.destroy', $this->lead->external_id));

        $response->assertStatus(302); // Redirect on success
        $this->assertSoftDeleted('leads', ['id' => $this->lead->id]);
    }

    #[Test]
    public function it_user_without_lead_delete_permission_cannot_delete_lead()
    {
        $this->actingAs($this->userWithoutPermission);

        $response = $this->json('DELETE', route('leads.destroy', $this->lead->external_id));

        $response->assertStatus(403);
        $this->assertDatabaseHas('leads', ['id' => $this->lead->id, 'deleted_at' => null]);
    }

    #[Test]
    public function it_lead_update_assign_only_accepts_user_assigned_id_field()
    {
        $user = User::factory()->create();
        $this->user = $user;
        $this->withPermissions(PermissionName::LEAD_ASSIGN);

        $newUser = User::factory()->create();
        $originalTitle = $this->lead->title;
        $originalDescription = $this->lead->description;

        $response = $this->json('PATCH', route('leads.updateAssign', $this->lead->external_id), [
            'user_assigned_id' => $newUser->id,
            'title' => 'Malicious Title Change',
            'description' => 'Malicious Description Change',
            'status_id' => 999,
        ]);

        $this->lead->refresh();

        $response->assertStatus(302);
        $this->assertEquals($newUser->id, $this->lead->user_assigned_id);
        // Verify mass assignment protection
        $this->assertEquals($originalTitle, $this->lead->title);
        $this->assertEquals($originalDescription, $this->lead->description);
        $this->assertNotEquals(999, $this->lead->status_id);
    }

    #[Test]
    public function it_lead_update_status_only_accepts_status_id_field()
    {
        $user = User::factory()->create();
        $this->user = $user;
        $this->withPermissions(PermissionName::LEAD_UPDATE_STATUS);

        $newStatus = Status::factory()->create(['source_type' => Lead::class]);
        while ($newStatus->id == $this->lead->status_id) {
            $newStatus = Status::factory()->create(['source_type' => Lead::class]);
        }

        $originalTitle = $this->lead->title;
        $originalDescription = $this->lead->description;

        $response = $this->json('PATCH', route('lead.update.status', $this->lead->external_id), [
            'status_id' => $newStatus->id,
            'title' => 'Malicious Title Change',
            'description' => 'Malicious Description Change',
            'user_assigned_id' => 999,
        ]);

        $this->lead->refresh();

        $response->assertStatus(302);
        $this->assertEquals($newStatus->id, $this->lead->status_id);
        // Verify mass assignment protection
        $this->assertEquals($originalTitle, $this->lead->title);
        $this->assertEquals($originalDescription, $this->lead->description);
        $this->assertNotEquals(999, $this->lead->user_assigned_id);
    }
}
