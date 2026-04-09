<?php

namespace Tests\Unit\Controllers\Project;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Permission;
use App\Models\Project;
use App\Models\Role;
use App\Models\Status;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[Group('authorization-fix')]
class ProjectAuthorizationTest extends TestCase
{
    use DatabaseTransactions;

    private Project $project;

    private User $userWithPermission;

    private User $userWithoutPermission;

    protected function setUp(): void
    {
        parent::setUp();

        $this->project = factory(Project::class)->create();

        // Create or find project-delete permission
        $deletePermission = Permission::firstOrCreate(
            ['name' => 'project-delete'],
            [
                'display_name' => 'Delete project',
                'description' => 'Permission to delete project',
                'grouping' => 'project',
            ]
        );

        // Create role with project-delete permission
        $roleWithPermission = Role::create([
            'name' => 'project-deleter',
            'display_name' => 'Project Deleter',
            'description' => 'Can delete projects',
            'external_id' => \Illuminate\Support\Str::uuid()->toString(),
        ]);
        $roleWithPermission->attachPermission($deletePermission);

        // Create role without project-delete permission
        $roleWithoutPermission = Role::create([
            'name' => 'project-viewer',
            'display_name' => 'Project Viewer',
            'description' => 'Cannot delete projects',
            'external_id' => \Illuminate\Support\Str::uuid()->toString(),
        ]);

        // Create users
        $this->userWithPermission = factory(User::class)->create();
        $this->userWithPermission->attachRole($roleWithPermission);

        $this->userWithoutPermission = factory(User::class)->create();
        $this->userWithoutPermission->attachRole($roleWithoutPermission);

        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    #[Test]
    public function user_with_project_delete_permission_can_delete_project()
    {
        $this->actingAs($this->userWithPermission);

        $response = $this->json('DELETE', route('projects.destroy', $this->project->external_id));

        $response->assertStatus(302); // Redirect on success
        $this->assertSoftDeleted('projects', ['id' => $this->project->id]);
    }

    #[Test]
    public function user_without_project_delete_permission_cannot_delete_project()
    {
        $this->actingAs($this->userWithoutPermission);

        $response = $this->json('DELETE', route('projects.destroy', $this->project->external_id));

        $response->assertStatus(403);
        $this->assertDatabaseHas('projects', ['id' => $this->project->id, 'deleted_at' => null]);
    }

    #[Test]
    public function user_with_assign_permission_can_update_project_assignment()
    {
        $roleWithPermission = Role::create([
            'name' => 'project-assigner',
            'display_name' => 'Project Assigner',
            'description' => 'Can assign projects',
            'external_id' => \Illuminate\Support\Str::uuid()->toString(),
        ]);
        $assignPermission = Permission::where('name', 'can-assign-new-user-to-project')->first();
        $roleWithPermission->attachPermission($assignPermission);

        $user = factory(User::class)->create();
        $user->attachRole($roleWithPermission);
        $this->actingAs($user);

        $newUser = factory(User::class)->create();

        $response = $this->json('PATCH', route('projects.updateAssign', $this->project->external_id), [
            'user_assigned_id' => $newUser->id,
        ]);

        $response->assertStatus(302);
        $this->assertEquals($newUser->id, $this->project->refresh()->user_assigned_id);
    }

    #[Test]
    public function user_without_assign_permission_cannot_update_project_assignment()
    {
        $this->actingAs($this->userWithoutPermission);

        $newUser = factory(User::class)->create();
        $originalAssignee = $this->project->user_assigned_id;

        $response = $this->json('PATCH', route('projects.updateAssign', $this->project->external_id), [
            'user_assigned_id' => $newUser->id,
        ]);

        $response->assertStatus(403);
        $this->assertEquals($originalAssignee, $this->project->refresh()->user_assigned_id);
    }

    #[Test]
    public function project_update_status_only_accepts_status_id_field()
    {
        $roleWithPermission = Role::create([
            'name' => 'status-updater',
            'display_name' => 'Status Updater',
            'description' => 'Can update status',
            'external_id' => \Illuminate\Support\Str::uuid()->toString(),
        ]);
        $statusPermission = Permission::where('name', 'project-update-status')->first();
        $roleWithPermission->attachPermission($statusPermission);

        $user = factory(User::class)->create();
        $user->attachRole($roleWithPermission);
        $this->actingAs($user);

        $newStatus = Status::typeOfProject()->where('id', '!=', $this->project->status_id)->first();
        $originalTitle = $this->project->title;
        $originalDescription = $this->project->description;

        $response = $this->json('PATCH', route('projects.updateStatus', $this->project->external_id), [
            'status_id' => $newStatus->id,
            'title' => 'Malicious Title Change',
            'description' => 'Malicious Description Change',
            'user_assigned_id' => 999,
        ]);

        $this->project->refresh();

        $response->assertStatus(302);
        $this->assertEquals($newStatus->id, $this->project->status_id);
        // Verify mass assignment protection
        $this->assertEquals($originalTitle, $this->project->title);
        $this->assertEquals($originalDescription, $this->project->description);
        $this->assertNotEquals(999, $this->project->user_assigned_id);
    }
}
