<?php

namespace Tests\Unit\Controllers\Project;

use App\Models\Lead;
use App\Models\Permission;
use App\Models\Project;
use App\Models\Status;
use App\Models\User;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

#[Group('security')]
#[Group('project-controller')]
class ProjectSecurityTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected $project;

    protected $unauthorizedUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->project = Project::factory()->create();

        // Create a user without project-delete permission
        $this->unauthorizedUser = User::factory()->withRole('employee')->create();
    }

    #[Test]
    public function authorized_user_can_delete_project()
    {
        // Give user permission to delete projects
        $permission = Permission::firstOrCreate(['name' => 'project-delete']);
        $this->user->roles->first()->attachPermission($permission);

        $response = $this->json('DELETE', route('projects.destroy', $this->project->external_id));

        $response->assertRedirect();
        $this->assertSoftDeleted('projects', ['id' => $this->project->id]);
    }

    #[Test]
    public function unauthorized_user_cannot_delete_project()
    {
        $this->actingAs($this->unauthorizedUser);

        $response = $this->json('DELETE', route('projects.destroy', $this->project->external_id));

        $response->assertStatus(403);
        $this->assertDatabaseHas('projects', ['id' => $this->project->id, 'deleted_at' => null]);
    }

    #[Test]
    public function update_status_only_accepts_status_id_field()
    {
        $permission = Permission::firstOrCreate(['name' => 'project-update-status']);
        $this->user->roles->first()->attachPermission($permission);
        $this->user = $this->user->fresh();
        $this->actingAs($this->user);

        $newStatus = Status::factory()->create(['source_type' => Project::class]);
        $originalAssignee = $this->project->user_assigned_id;

        // Attempt to change both status_id and user_assigned_id (mass assignment attack)
        $response = $this->json('PATCH', route('project.update.status', $this->project->external_id), [
            'status_id' => $newStatus->id,
            'user_assigned_id' => $this->user->id, // This should be ignored
            'title' => 'Hacked Title', // This should be ignored
        ]);

        $this->project->refresh();

        // Status should be updated
        $this->assertEquals($newStatus->id, $this->project->status_id);

        // But user_assigned_id should NOT be changed (mass assignment protection)
        $this->assertEquals($originalAssignee, $this->project->user_assigned_id);

        // Title should not be changed
        $this->assertNotEquals('Hacked Title', $this->project->title);
    }

    #[Test]
    public function update_status_with_invalid_status_external_id_returns_error()
    {
        $permission = Permission::firstOrCreate(['name' => 'task-update-status']);
        $this->user->roles->first()->attachPermission($permission);

        $response = $this->json('PATCH', route('project.update.status', $this->project->external_id), [
            'statusExternalId' => 'invalid-uuid-12345',
        ], ['X-Requested-With' => 'XMLHttpRequest']);

        $response->assertStatus(400)
            ->assertJson(['error' => __('Invalid status')]);
    }

    #[Test]
    public function update_status_via_ajax_with_valid_external_id()
    {
        $permission = Permission::firstOrCreate(['name' => 'project-update-status']);
        $this->user->roles->first()->attachPermission($permission);
        $this->user = $this->user->fresh();
        $this->actingAs($this->user);

        $newStatus = Status::factory()->create(['source_type' => Project::class]);

        $response = $this->json('PATCH', route('project.update.status', $this->project->external_id), [
            'statusExternalId' => $newStatus->external_id,
        ], ['X-Requested-With' => 'XMLHttpRequest']);

        $this->project->refresh();
        $this->assertEquals($newStatus->id, $this->project->status_id);
    }

    #[Test]
    public function update_status_rejects_invalid_status_type()
    {
        $permission = Permission::firstOrCreate(['name' => 'project-update-status']);
        $this->user->roles->first()->attachPermission($permission);

        // Create a status that belongs to a different type (Lead instead of Project)
        $leadStatus = Status::factory()->create(['source_type' => Lead::class]);
        $originalStatus = $this->project->status_id;

        // Attempt to assign a Lead status to a Project
        $response = $this->json('PATCH', route('project.update.status', $this->project->external_id), [
            'status_id' => $leadStatus->id,
        ]);

        $this->project->refresh();

        // Status should NOT be changed because it's not a valid project status
        $this->assertEquals($originalStatus, $this->project->status_id);

        // Should show warning message
        $response->assertRedirect();
        $response->assertSessionHas('flash_message_warning', __('Invalid status for project'));
    }

    #[Test]
    public function update_status_rejects_nonexistent_status_id()
    {
        $permission = Permission::firstOrCreate(['name' => 'project-update-status']);
        $this->user->roles->first()->attachPermission($permission);

        $originalStatus = $this->project->status_id;

        // Attempt to assign a non-existent status ID
        $response = $this->json('PATCH', route('project.update.status', $this->project->external_id), [
            'status_id' => 999999,
        ]);

        $this->project->refresh();

        // Status should NOT be changed
        $this->assertEquals($originalStatus, $this->project->status_id);

        // Should show warning message
        $response->assertRedirect();
        $response->assertSessionHas('flash_message_warning', __('Invalid status for project'));
    }
}
