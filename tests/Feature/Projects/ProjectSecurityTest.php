<?php

namespace Tests\Feature\Projects;

use App\Enums\PermissionName;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Status;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

#[Group('security')]
#[Group('project-controller')]
class ProjectSecurityTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected Project $project;

    protected User $unauthorizedUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->project          = Project::factory()->create();
        $this->unauthorizedUser = User::factory()->withRole('employee')->create();
    }

    #[Test]
    public function it_authorized_user_can_delete_project()
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
    public function it_unauthorized_user_cannot_delete_project()
    {
        /* Arrange */
        $this->actingAs($this->unauthorizedUser);

        /* Act */
        $response = $this->json('DELETE', route('projects.destroy', $this->project->external_id));

        /* Assert */
        $response->assertStatus(403);
        $this->assertDatabaseHas('projects', ['id' => $this->project->id, 'deleted_at' => null]);
    }

    #[Test]
    public function it_updates_status_only_accepts_status_id_field()
    {
        /* Arrange */
        $this->withPermissions(PermissionName::PROJECT_UPDATE_STATUS);

        $newStatus        = Status::factory()->create(['source_type' => Project::class]);
        $originalAssignee = $this->project->user_assigned_id;

        /* Act */
        $response = $this->json('PATCH', route('project.update.status', $this->project->external_id), [
            'status_id'        => $newStatus->id,
            'user_assigned_id' => $this->user->id,
            'title'            => 'Hacked Title',
        ]);
        $this->project->refresh();

        /* Assert */
        $this->assertEquals($newStatus->id, $this->project->status_id);
        $this->assertEquals($originalAssignee, $this->project->user_assigned_id);
        $this->assertNotEquals('Hacked Title', $this->project->title);
    }

    #[Test]
    public function it_updates_status_with_invalid_status_external_id_returns_error()
    {
        /* Arrange */
        $this->withPermissions(PermissionName::PROJECT_UPDATE_STATUS);

        /* Act */
        $response = $this->json('PATCH', route('project.update.status', $this->project->external_id), [
            'statusExternalId' => 'invalid-uuid-12345',
        ], ['X-Requested-With' => 'XMLHttpRequest']);

        /* Assert */
        $response->assertStatus(400)
            ->assertJson(['error' => __('Invalid status')]);
    }

    #[Test]
    public function it_updates_status_via_ajax_with_valid_external_id()
    {
        /* Arrange */
        $this->withPermissions(PermissionName::PROJECT_UPDATE_STATUS);

        $newStatus = Status::factory()->create(['source_type' => Project::class]);

        /* Act */
        $response = $this->json('PATCH', route('project.update.status', $this->project->external_id), [
            'statusExternalId' => $newStatus->external_id,
        ], ['X-Requested-With' => 'XMLHttpRequest']);

        /* Assert */
        $this->project->refresh();
        $this->assertEquals($newStatus->id, $this->project->status_id);
    }

    #[Test]
    public function it_updates_status_rejects_invalid_status_type()
    {
        /* Arrange */
        $this->withPermissions(PermissionName::PROJECT_UPDATE_STATUS);

        $leadStatus     = Status::factory()->create(['source_type' => Lead::class]);
        $originalStatus = $this->project->status_id;

        /* Act */
        $response = $this->json('PATCH', route('project.update.status', $this->project->external_id), [
            'status_id' => $leadStatus->id,
        ]);
        $this->project->refresh();

        /* Assert */
        $this->assertEquals($originalStatus, $this->project->status_id);
        $response->assertRedirect();
        $response->assertSessionHas('flash_message_warning', __('Invalid status for project'));
    }

    #[Test]
    public function it_updates_status_rejects_nonexistent_status_id()
    {
        /* Arrange */
        $this->withPermissions(PermissionName::PROJECT_UPDATE_STATUS);

        $originalStatus = $this->project->status_id;

        /* Act */
        $response = $this->json('PATCH', route('project.update.status', $this->project->external_id), [
            'status_id' => 999999,
        ]);
        $this->project->refresh();

        /* Assert */
        $this->assertEquals($originalStatus, $this->project->status_id);
        $response->assertRedirect();
        $response->assertSessionHas('flash_message_warning', __('Invalid status for project'));
    }
}
