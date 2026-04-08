<?php

namespace Tests\Unit\Controllers\Lead;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Lead;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Status;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[Group('authorization-fix')]
class LeadAuthorizationTest extends TestCase
{
    use DatabaseTransactions;

    private Lead $lead;
    private User $userWithPermission;
    private User $userWithoutPermission;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lead = factory(Lead::class)->create();

        // Create role with lead-delete permission
        $roleWithPermission = Role::create([
            'name' => 'lead-deleter',
            'display_name' => 'Lead Deleter',
            'description' => 'Can delete leads',
        ]);
        $deletePermission = Permission::where('name', 'lead-delete')->first();
        $roleWithPermission->attachPermission($deletePermission);

        // Create role without lead-delete permission
        $roleWithoutPermission = Role::create([
            'name' => 'lead-viewer',
            'display_name' => 'Lead Viewer',
            'description' => 'Cannot delete leads',
        ]);

        // Create users
        $this->userWithPermission = factory(User::class)->create();
        $this->userWithPermission->attachRole($roleWithPermission);

        $this->userWithoutPermission = factory(User::class)->create();
        $this->userWithoutPermission->attachRole($roleWithoutPermission);

        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    #[Test]
    public function user_with_lead_delete_permission_can_delete_lead()
    {
        $this->actingAs($this->userWithPermission);

        $response = $this->json('DELETE', route('leads.destroy', $this->lead->external_id));

        $response->assertStatus(302); // Redirect on success
        $this->assertSoftDeleted('leads', ['id' => $this->lead->id]);
    }

    #[Test]
    public function user_without_lead_delete_permission_cannot_delete_lead()
    {
        $this->actingAs($this->userWithoutPermission);

        $response = $this->json('DELETE', route('leads.destroy', $this->lead->external_id));

        $response->assertStatus(403);
        $this->assertDatabaseHas('leads', ['id' => $this->lead->id, 'deleted_at' => null]);
    }

    #[Test]
    public function lead_update_assign_only_accepts_user_assigned_id_field()
    {
        $roleWithPermission = Role::create([
            'name' => 'lead-assigner',
            'display_name' => 'Lead Assigner',
            'description' => 'Can assign leads',
        ]);
        $assignPermission = Permission::where('name', 'can-assign-new-user-to-lead')->first();
        $roleWithPermission->attachPermission($assignPermission);

        $user = factory(User::class)->create();
        $user->attachRole($roleWithPermission);
        $this->actingAs($user);

        $newUser = factory(User::class)->create();
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
    public function lead_update_status_only_accepts_status_id_field()
    {
        $roleWithPermission = Role::create([
            'name' => 'lead-status-updater',
            'display_name' => 'Lead Status Updater',
            'description' => 'Can update lead status',
        ]);
        $statusPermission = Permission::where('name', 'lead-update-status')->first();
        $roleWithPermission->attachPermission($statusPermission);

        $user = factory(User::class)->create();
        $user->attachRole($roleWithPermission);
        $this->actingAs($user);

        $newStatus = Status::typeOfLead()->where('id', '!=', $this->lead->status_id)->first();
        $originalTitle = $this->lead->title;
        $originalDescription = $this->lead->description;

        $response = $this->json('PATCH', route('leads.updateStatus', $this->lead->external_id), [
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
