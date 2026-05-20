<?php

namespace Tests\Feature\Projects;

use App\Enums\PermissionName;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Project;
use App\Models\Status;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

#[Group('authorization-fix')]
class ProjectAuthorizationTest extends AbstractTestCase
{
    use RefreshDatabase;

    private Project $project;

    private User $userWithoutPermission;

    protected function setUp(): void
    {
        parent::setUp();

        $this->project               = Project::factory()->create();
        $this->userWithoutPermission = User::factory()->create();

        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    #[Test]
    public function it_user_with_project_delete_permission_can_delete_project()
    {
        /* Arrange */
        $this->withPermissions(PermissionName::PROJECT_DELETE);

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
        $this->withPermissions(PermissionName::PROJECT_ASSIGN);
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
        $this->withPermissions(PermissionName::PROJECT_UPDATE_STATUS);

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
