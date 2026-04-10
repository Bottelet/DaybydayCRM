<?php

namespace Tests\Unit\Controllers\Lead;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Lead;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Status;
use App\Models\User;
use Illuminate\Support\Str;
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

        // Create or get the lead-delete permission
        $deletePermission = Permission::firstOrCreate(
            ['name' => 'lead-delete'],
            [
                'display_name' => 'Delete lead',
                'description' => 'Permission to delete lead',
                'grouping' => 'lead',
                'external_id' => Str::uuid()->toString(),
            ]
        );

        // Create role with lead-delete permission
        $roleWithPermission = Role::create([
            'name' => 'lead-deleter',
            'display_name' => 'Lead Deleter',
            'description' => 'Can delete leads',
            'external_id' => Str::uuid()->toString(),
        ]);
        $roleWithPermission->attachPermission($deletePermission);

        // Create role without lead-delete permission
        $roleWithoutPermission = Role::create([
            'name' => 'lead-viewer',
            'display_name' => 'Lead Viewer',
            'description' => 'Cannot delete leads',
            'external_id' => Str::uuid()->toString(),
        ]);

        // Create users
        $this->userWithPermission = User::factory()->create();
        $this->userWithPermission->attachRole($roleWithPermission);

        $this->userWithoutPermission = User::factory()->create();
        $this->userWithoutPermission->attachRole($roleWithoutPermission);

        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    #[Test]
    public function user_with_lead_delete_permission_can_delete_lead()
    {
        $this->actingAs($this->userWithPermission);

        // Clear permission cache to ensure fresh permission check
        \Illuminate\Support\Facades\Cache::tags('role_user')->flush();

        $response = $this->delete(route('leads.destroy', $this->lead->external_id));

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
        // Create or get the permission
        $assignPermission = Permission::firstOrCreate(
            ['name' => 'can-assign-new-user-to-lead'],
            [
                'display_name' => 'Assign users to leads',
                'description' => 'Can assign users to leads',
                'grouping' => 'lead',
                'external_id' => Str::uuid()->toString(),
            ]
        );

        $roleWithPermission = Role::create([
            'name' => 'lead-assigner',
            'display_name' => 'Lead Assigner',
            'description' => 'Can assign leads',
            'external_id' => Str::uuid()->toString(),
        ]);
        $roleWithPermission->attachPermission($assignPermission);

        $user = User::factory()->create();
        $user->attachRole($roleWithPermission);
        $this->actingAs($user);

        // Clear permission cache to ensure fresh permission check
        \Illuminate\Support\Facades\Cache::tags('role_user')->flush();

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
    public function lead_update_status_only_accepts_status_id_field()
    {
        // Create or get the permission
        $statusPermission = Permission::firstOrCreate(
            ['name' => 'lead-update-status'],
            [
                'display_name' => 'Update lead status',
                'description' => 'Permission to update lead status',
                'grouping' => 'lead',
                'external_id' => Str::uuid()->toString(),
            ]
        );

        $roleWithPermission = Role::create([
            'name' => 'lead-status-updater',
            'display_name' => 'Lead Status Updater',
            'description' => 'Can update lead status',
            'external_id' => Str::uuid()->toString(),
        ]);
        $roleWithPermission->attachPermission($statusPermission);

        $user = User::factory()->create();
        $user->attachRole($roleWithPermission);
        $this->actingAs($user);

        // Clear permission cache to ensure fresh permission check
        \Illuminate\Support\Facades\Cache::tags('role_user')->flush();

        $newStatus = Status::factory()->create(['source_type' => 'lead']);
        while ($newStatus->id == $this->lead->status_id) {
            $newStatus = Status::factory()->create(['source_type' => 'lead']);
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
