<?php

namespace Tests\Feature\Controllers\Project;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Permission;
use App\Models\Project;
use App\Models\Role;
use App\Models\Status;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

#[Group('authorization-fix')]
class ProjectAuthorizationTest extends AbstractTestCase
{
    use RefreshDatabase;

    private Project $project;

    private User $userWithPermission;

    private User $userWithoutPermission;

    protected function setUp(): void
    {
        parent::setUp();

        /* Arrange */
        $this->project = Project::factory()->create();
        $deletePermission = Permission::firstOrCreate(
            ['name' => 'project-delete'],
            [
                'display_name' => 'Delete project',
                'description'  => 'Permission to delete project',
                'grouping'     => 'project',
            ]
        );
        $roleWithPermission = Role::create([
            'name'         => 'project-deleter',
            'display_name' => 'Project Deleter',
            'description'  => 'Can delete projects',
            'external_id'  => Str::uuid()->toString(),
        ]);
        $roleWithPermission->attachPermission($deletePermission);
        $roleWithoutPermission = Role::create([
            'name'         => 'project-viewer',
            'display_name' => 'Project Viewer',
            'description'  => 'Cannot delete projects',
            'external_id'  => Str::uuid()->toString(),
        ]);
        $this->userWithPermission = User::factory()->create();
        $this->userWithPermission->attachRole($roleWithPermission);
        $this->userWithoutPermission = User::factory()->create();
        $this->userWithoutPermission->attachRole($roleWithoutPermission);
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    #[Test]
    public function it_user_with_project_delete_permission_can_delete_project()
    {
        /* Arrange */
        $this->actingAs($this->userWithPermission);
        \Illuminate\Support\Facades\Cache::tags('role_user')->flush();
        $this->userWithPermission = $this->userWithPermission->fresh();

        /* Act */
        $response = $this->json('DELETE', route('projects.destroy', $this->project->external_id));

        /* Assert */
        $response->assertStatus(200);
        $this->assertSoftDeleted('projects', ['id' => $this->project->id]);
    }

    #[Test]
    public function it_user_without_project_delete_permission_cannot_delete_project()
    {
        /* Arrange */
        $this->actingAs($this->userWithoutPermission);

        /* Act */
        $response = $this->json('DELETE', route('projects.destroy', $this->project->external_id));

        /* Assert */
        $response->assertStatus(403);
        $this->assertDatabaseHas('projects', ['id' => $this->project->id, 'deleted_at' => null]);
    }

    #[Test]
    public function it_user_with_assign_permission_can_update_project_assignment()
    {
        /* Arrange */
        $roleWithPermission = Role::create([
            'name'         => 'project-assigner',
            'display_name' => 'Project Assigner',
            'description'  => 'Can assign projects',
            'external_id'  => Str::uuid()->toString(),
        ]);
        $assignPermission = Permission::firstOrCreate(['name' => 'can-assign-new-user-to-project']);
        $roleWithPermission->attachPermission($assignPermission);
        $user = User::factory()->create();
        $user->attachRole($roleWithPermission);
        $this->actingAs($user);
        \Illuminate\Support\Facades\Cache::tags('role_user')->flush();
        $user = $user->fresh();
        $newUser = User::factory()->create();

        /* Act */
        $response = $this->json('PATCH', route('project.update.assignee', $this->project->external_id), [
            'user_assigned_id' => $newUser->id,
        ]);

        /* Assert */
        $response->assertStatus(302);
        $this->assertEquals($newUser->id, $this->project->refresh()->user_assigned_id);
    }

    #[Test]
    public function it_user_without_assign_permission_cannot_update_project_assignment()
    {
        /* Arrange */
        $this->actingAs($this->userWithoutPermission);
        $newUser          = User::factory()->create();
        $originalAssignee = $this->project->user_assigned_id;

        /* Act */
        $response = $this->json('PATCH', route('project.update.assignee', $this->project->external_id), [
            'user_assigned_id' => $newUser->id,
        ]);

        /* Assert */
        $response->assertStatus(403);
        $this->assertEquals($originalAssignee, $this->project->refresh()->user_assigned_id);
    }

    #[Test]
    public function it_project_update_status_only_accepts_status_id_field()
    {
        /* Arrange */
        $roleWithPermission = Role::create([
            'name'         => 'status-updater',
            'display_name' => 'Status Updater',
            'description'  => 'Can update status',
            'external_id'  => Str::uuid()->toString(),
        ]);
        $statusPermission = Permission::firstOrCreate(['name' => 'project-update-status']);
        $roleWithPermission->attachPermission($statusPermission);
        $user = User::factory()->create();
        $user->attachRole($roleWithPermission);
        $this->actingAs($user);
        \Illuminate\Support\Facades\Cache::tags('role_user')->flush();
        $user = $user->fresh();
        $newStatus = Status::factory()->create(['source_type' => Project::class]);
        while ($newStatus->id == $this->project->status_id) {
            $newStatus = Status::factory()->create(['source_type' => Project::class]);
        }
        $originalTitle       = $this->project->title;
        $originalDescription = $this->project->description;

        /* Act */
        $response = $this->json('PATCH', route('project.update.status', $this->project->external_id), [
            'status_id'        => $newStatus->id,
            'title'            => 'Malicious Title Change',
            'description'      => 'Malicious Description Change',
            'user_assigned_id' => 999,
        ]);
        $this->project->refresh();

        /* Assert */
        $response->assertStatus(302);
        $this->assertEquals($newStatus->id, $this->project->status_id);
        $this->assertEquals($originalTitle, $this->project->title);
        $this->assertEquals($originalDescription, $this->project->description);
        $this->assertNotEquals(999, $this->project->user_assigned_id);
    }
}
