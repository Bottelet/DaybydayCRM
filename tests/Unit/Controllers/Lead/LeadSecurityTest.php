<?php

namespace Tests\Unit\Controllers\Lead;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Lead;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

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

        // Create and authenticate a user with default role
        $this->user = User::factory()->withRole('employee')->create();
        $this->actingAs($this->user);

        // Create a user without lead-delete permission
        $this->unauthorizedUser = User::factory()->withRole('employee')->create();

        // Disable CSRF middleware for all tests
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    #[Test]
    public function authorized_user_can_delete_lead()
    {
        // Give user permission to delete leads
        $permission = Permission::firstOrCreate(['name' => 'lead-delete']);
        $this->user->roles->first()->attachPermission($permission);

        // Clear permission cache to ensure fresh permission check
        \Illuminate\Support\Facades\Cache::tags('role_user')->flush();

        $response = $this->json('DELETE', route('leads.destroy', $this->lead->external_id));

        $response->assertRedirect();
        $this->assertSoftDeleted('leads', ['id' => $this->lead->id]);
    }

    #[Test]
    public function unauthorized_user_cannot_delete_lead()
    {
        $this->actingAs($this->unauthorizedUser);

        $response = $this->json('DELETE', route('leads.destroy', $this->lead->external_id));

        $response->assertRedirect();
        $response->assertSessionHas('flash_message_warning');
        $this->assertDatabaseHas('leads', ['id' => $this->lead->id, 'deleted_at' => null]);
    }

    #[Test]
    public function unauthorized_user_cannot_delete_lead_via_json()
    {
        $this->actingAs($this->unauthorizedUser);

        $response = $this->json('DELETE', '/leads/'.$this->lead->external_id.'/json');

        $response->assertStatus(403);
        $this->assertDatabaseHas('leads', ['id' => $this->lead->id, 'deleted_at' => null]);
    }

    #[Test]
    public function update_assign_only_accepts_user_assigned_id_field()
    {
        $permission = Permission::firstOrCreate(['name' => 'lead-assigned']);
        $this->user->roles->first()->attachPermission($permission);

        // Clear permission cache to ensure fresh permission check
        \Illuminate\Support\Facades\Cache::tags('role_user')->flush();

        $newUser = User::factory()->create();
        $originalStatus = $this->lead->status_id;
        $originalTitle = $this->lead->title;

        // Use PATCH (route is PATCH)
        $response = $this->json('PATCH', route('leads.updateAssign', $this->lead->external_id), [
            'user_assigned_id' => $newUser->id,
            'status_id' => 999, // This should be ignored
            'title' => 'Hacked Title', // This should be ignored
        ]);

        $this->lead->refresh();

        // user_assigned_id should be updated
        $this->assertEquals($newUser->id, $this->lead->user_assigned_id);

        // But status_id should NOT be changed (mass assignment protection)
        $this->assertEquals($originalStatus, $this->lead->status_id);

        // Title should not be changed
        $this->assertEquals($originalTitle, $this->lead->title);
    }

    #[Test]
    public function update_status_only_accepts_status_id_field()
    {
        $permission = Permission::firstOrCreate(['name' => 'lead-update-status']);
        $this->user->roles->first()->attachPermission($permission);

        // Clear permission cache to ensure fresh permission check
        \Illuminate\Support\Facades\Cache::tags('role_user')->flush();

        $newStatus = Status::factory()->create(['source_type' => Lead::class]);
        $originalAssignee = $this->lead->user_assigned_id;

        // Use PATCH (route is PATCH)
        $response = $this->json('PATCH', route('lead.update.status', $this->lead->external_id), [
            'status_id' => $newStatus->id,
            'user_assigned_id' => $this->user->id, // This should be ignored
            'title' => 'Hacked Title', // This should be ignored
        ]);

        $this->lead->refresh();

        // Status should be updated
        $this->assertEquals($newStatus->id, $this->lead->status_id);

        // But user_assigned_id should NOT be changed (mass assignment protection)
        $this->assertEquals($originalAssignee, $this->lead->user_assigned_id);

        // Title should not be changed
        $this->assertNotEquals('Hacked Title', $this->lead->title);
    }

    #[Test]
    public function update_status_rejects_invalid_status_type()
    {
        $permission = Permission::firstOrCreate(['name' => 'lead-update-status']);
        $this->user->roles->first()->attachPermission($permission);

        // Clear permission cache to ensure fresh permission check
        \Illuminate\Support\Facades\Cache::tags('role_user')->flush();

        // Create a status that belongs to a different type (Task instead of Lead)
        $taskStatus = Status::factory()->create(['source_type' => Task::class]);
        $originalStatus = $this->lead->status_id;

        // Use PATCH (route is PATCH)
        $response = $this->json('PATCH', route('lead.update.status', $this->lead->external_id), [
            'status_id' => $taskStatus->id,
        ]);

        $this->lead->refresh();

        // Status should NOT be changed because it's not a valid lead status
        $this->assertEquals($originalStatus, $this->lead->status_id);

        // Should show warning message
        $response->assertRedirect();
        $response->assertSessionHas('flash_message_warning', __('Invalid status for lead'));
    }

    #[Test]
    public function update_status_rejects_nonexistent_status_id()
    {
        $permission = Permission::firstOrCreate(['name' => 'lead-update-status']);
        $this->user->roles->first()->attachPermission($permission);

        // Clear permission cache to ensure fresh permission check
        \Illuminate\Support\Facades\Cache::tags('role_user')->flush();

        $originalStatus = $this->lead->status_id;

        // Use PATCH (route is PATCH)
        $response = $this->json('PATCH', route('lead.update.status', $this->lead->external_id), [
            'status_id' => 999999,
        ]);

        $this->lead->refresh();

        // Status should NOT be changed
        $this->assertEquals($originalStatus, $this->lead->status_id);

        // Should show warning message
        $response->assertRedirect();
        $response->assertSessionHas('flash_message_warning', __('Invalid status for lead'));
    }
}
