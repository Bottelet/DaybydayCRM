<?php

namespace Tests\Unit\Controllers\Lead;

use App\Models\Lead;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[Group('security')]
#[Group('lead-controller')]
class LeadSecurityTest extends TestCase
{
    use DatabaseTransactions;

    protected $lead;

    protected $unauthorizedUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lead = factory(Lead::class)->create();

        // Create a user without lead-delete permission
        $this->unauthorizedUser = factory(User::class)->create();
        $role = Role::where('name', 'employee')->first();
        $this->unauthorizedUser->attachRole($role);
    }

    #[Test]
    public function authorized_user_can_delete_lead()
    {
        // Give user permission to delete leads
        $permission = Permission::firstOrCreate(['name' => 'lead-delete']);
        $this->user->roles->first()->attachPermission($permission);

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

        $response = $this->json('DELETE', route('leads.destroy.json', $this->lead->external_id));

        $response->assertStatus(403);
        $this->assertDatabaseHas('leads', ['id' => $this->lead->id, 'deleted_at' => null]);
    }

    #[Test]
    public function update_assign_only_accepts_user_assigned_id_field()
    {
        $permission = Permission::firstOrCreate(['name' => 'lead-assigned']);
        $this->user->roles->first()->attachPermission($permission);

        $newUser = factory(User::class)->create();
        $originalStatus = $this->lead->status_id;
        $originalTitle = $this->lead->title;

        // Attempt to change multiple fields (mass assignment attack)
        $response = $this->json('PATCH', route('lead.update.assignee', $this->lead->external_id), [
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

        $newStatus = factory(Status::class)->create(['source_type' => Lead::class]);
        $originalAssignee = $this->lead->user_assigned_id;

        // Attempt to change both status_id and user_assigned_id (mass assignment attack)
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

        // Create a status that belongs to a different type (Task instead of Lead)
        $taskStatus = factory(Status::class)->create(['source_type' => Task::class]);
        $originalStatus = $this->lead->status_id;

        // Attempt to assign a Task status to a Lead
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

        $originalStatus = $this->lead->status_id;

        // Attempt to assign a non-existent status ID
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
