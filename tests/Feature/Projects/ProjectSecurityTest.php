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
        $response = $this->delete(route('projects.destroy', $this->project->external_id), [], ['Accept' => 'application/json']);

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
        $response = $this->delete(route('projects.destroy', $this->project->external_id), [], ['Accept' => 'application/json']);

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
        $response = $this->patch(route('project.update.status', $this->project->external_id), [
            'status_id'        => $newStatus->id,
            'user_assigned_id' => $this->user->id,
            'title'            => 'Hacked Title',
        ]);
        $this->project->refresh();

        /* Assert */
        $response->assertStatus(302);
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
        $response = $this->patch(route('project.update.status', $this->project->external_id), [
            'statusExternalId' => 'invalid-uuid-12345',
        ], ['Accept' => 'application/json']);

        /* Assert */
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['statusExternalId' => 'The selected status external id is invalid.']);
    }

    #[Test]
    public function it_updates_status_via_ajax_with_valid_external_id()
    {
        /* Arrange */
        $this->withPermissions(PermissionName::PROJECT_UPDATE_STATUS);

        $newStatus = Status::factory()->create(['source_type' => Project::class]);

        /* Act */
        $response = $this->patch(route('project.update.status', $this->project->external_id), [
            'statusExternalId' => $newStatus->external_id,
        ], ['X-Requested-With' => 'XMLHttpRequest']);

        /* Assert: the X-Requested-With header alone doesn't satisfy
         * expectsJson() without a matching Accept header, so this still
         * gets the redirect() path, not the JSON one. */
        $response->assertStatus(302);
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
        $response = $this->from(route('projects.show', $this->project->external_id))
            ->patch(route('project.update.status', $this->project->external_id), [
                'status_id' => $leadStatus->id,
            ]);
        $this->project->refresh();

        /* Assert */
        $this->assertEquals($originalStatus, $this->project->status_id);
        $response->assertRedirect(route('projects.show', $this->project->external_id));
        $response->assertSessionHasErrors(['status_id' => __('Invalid status for project')]);
    }

    #[Test]
    public function it_updates_status_rejects_nonexistent_status_id()
    {
        /* Arrange */
        $this->withPermissions(PermissionName::PROJECT_UPDATE_STATUS);

        $originalStatus = $this->project->status_id;

        /* Act */
        $response = $this->from(route('projects.show', $this->project->external_id))
            ->patch(route('project.update.status', $this->project->external_id), [
                'status_id' => 999999,
            ]);
        $this->project->refresh();

        /* Assert */
        $this->assertEquals($originalStatus, $this->project->status_id);
        $response->assertRedirect(route('projects.show', $this->project->external_id));
        $response->assertSessionHasErrors(['status_id' => __('The selected status id is invalid.')]);
    }

    #[Test]
    public function it_authorized_user_can_update_assign()
    {
        /* Arrange */
        $this->withPermissions(PermissionName::PROJECT_ASSIGN);
        $newAssignee = User::factory()->create();

        /* Act */
        $response = $this->patch(route('project.update.assignee', $this->project->external_id), [
            'user_assigned_id' => $newAssignee->id,
        ]);

        /* Assert */
        $response->assertStatus(302);
        $this->project->refresh();
        $this->assertEquals($newAssignee->id, $this->project->user_assigned_id);
    }

    #[Test]
    public function it_unauthorized_user_cannot_update_assign()
    {
        /* Arrange */
        $this->actingAs($this->unauthorizedUser);
        $originalAssignee = $this->project->user_assigned_id;
        $newAssignee      = User::factory()->create();

        /* Act */
        $response = $this->from(route('projects.show', $this->project->external_id))
            ->patch(route('project.update.assignee', $this->project->external_id), [
                'user_assigned_id' => $newAssignee->id,
            ]);

        /* Assert: a controller-constructor middleware (not the FormRequest's own
         * authorize()) gates this route and fires first, with its own message. */
        $response->assertRedirect(route('projects.show', $this->project->external_id));
        $response->assertSessionHas(
            'flash_message_warning',
            __('You do not have permission to assign users to this project')
        );
        $this->project->refresh();
        $this->assertEquals($originalAssignee, $this->project->user_assigned_id);
    }

    #[Test]
    public function it_unauthorized_user_cannot_update_status()
    {
        /* Arrange */
        $this->actingAs($this->unauthorizedUser);
        $originalStatus = $this->project->status_id;
        $newStatus      = Status::factory()->create(['source_type' => Project::class]);

        /* Act */
        $response = $this->from(route('projects.show', $this->project->external_id))
            ->patch(route('project.update.status', $this->project->external_id), [
                'status_id' => $newStatus->id,
            ]);
        $this->project->refresh();

        /* Assert: UpdateProjectStatusRequest::authorize() denies before the
         * validator or controller ever run. The app's exception Handler
         * converts that AuthorizationException into a redirect-back +
         * flash_message_warning, not a bare 403. */
        $response->assertRedirect(route('projects.show', $this->project->external_id));
        $response->assertSessionHas('flash_message_warning', 'This action is unauthorized.');
        $this->assertEquals($originalStatus, $this->project->status_id);
    }

    #[Test]
    public function it_updating_project_rejects_missing_required_fields()
    {
        /* Arrange */
        $this->withPermissions(PermissionName::PROJECT_UPDATE);
        $originalTitle       = $this->project->title;
        $originalDescription = $this->project->description;

        /* Act */
        $response = $this->from(route('projects.edit', $this->project->external_id))
            ->patch(route('projects.update', $this->project->external_id), [
                'title'       => '',
                'description' => '',
            ]);
        $this->project->refresh();

        /* Assert */
        $response->assertRedirect(route('projects.edit', $this->project->external_id));
        $response->assertSessionHasErrors(['title', 'description']);
        $this->assertEquals($originalTitle, $this->project->title);
        $this->assertEquals($originalDescription, $this->project->description);
    }

    #[Test]
    public function it_unauthorized_user_cannot_update_project()
    {
        /* Arrange */
        $this->actingAs($this->unauthorizedUser);
        $originalTitle = $this->project->title;

        /* Act */
        $response = $this->from(route('projects.show', $this->project->external_id))
            ->patch(route('projects.update', $this->project->external_id), [
                'title'       => 'Hacked Title',
                'description' => 'Hacked description',
            ]);
        $this->project->refresh();

        /* Assert: UpdateProjectRequest::authorize() denies before the
         * validator or controller ever run. The app's exception Handler
         * converts that AuthorizationException into a redirect-back +
         * flash_message_warning, not a bare 403. */
        $response->assertRedirect(route('projects.show', $this->project->external_id));
        $response->assertSessionHas('flash_message_warning', 'This action is unauthorized.');
        $this->assertEquals($originalTitle, $this->project->title);
    }

    #[Test]
    public function it_updating_deadline_rejects_invalid_deadline_date()
    {
        /* Arrange */
        $this->withPermissions(PermissionName::PROJECT_UPDATE_DEADLINE);
        $originalDeadline = $this->project->deadline->toISOString();

        /* Act */
        $response = $this->from(route('projects.show', $this->project->external_id))
            ->patch(route('project.update.deadline', $this->project->external_id), [
                'deadline_date' => 'not-a-date',
            ]);
        $this->project->refresh();

        /* Assert */
        $response->assertRedirect(route('projects.show', $this->project->external_id));
        $response->assertSessionHasErrors(['deadline_date']);
        $this->assertEquals($originalDeadline, $this->project->deadline->toISOString());
    }

    #[Test]
    public function it_unauthorized_user_cannot_update_deadline()
    {
        /* Arrange */
        $this->actingAs($this->unauthorizedUser);
        $originalDeadline = $this->project->deadline->toISOString();

        /* Act */
        $response = $this->from(route('projects.show', $this->project->external_id))
            ->patch(route('project.update.deadline', $this->project->external_id), [
                'deadline_date' => '2030-01-01',
            ]);
        $this->project->refresh();

        /* Assert: UpdateProjectDeadlineRequest::authorize() denies before the
         * validator or controller ever run. The app's exception Handler
         * converts that AuthorizationException into a redirect-back +
         * flash_message_warning, not a bare 403. */
        $response->assertRedirect(route('projects.show', $this->project->external_id));
        $response->assertSessionHas('flash_message_warning', 'This action is unauthorized.');
        $this->assertEquals($originalDeadline, $this->project->deadline->toISOString());
    }

    #[Test]
    public function it_creating_project_rejects_missing_required_fields()
    {
        /* Arrange */
        $this->withPermissions(PermissionName::PROJECT_CREATE);
        $countBefore = Project::count();

        /* Act */
        $response = $this->from(route('projects.create'))
            ->post(route('projects.store'), []);

        /* Assert */
        $response->assertRedirect(route('projects.create'));
        $response->assertSessionHasErrors(['title', 'description', 'status_id', 'user_assigned_id', 'client_external_id']);
        $this->assertEquals($countBefore, Project::count());
    }

    #[Test]
    public function it_unauthorized_user_cannot_create_project()
    {
        /* Arrange */
        $this->actingAs($this->unauthorizedUser);
        $countBefore = Project::count();

        /* Act */
        $response = $this->from(route('projects.create'))
            ->post(route('projects.store'), [
                'title' => 'Malicious Project',
            ]);

        /* Assert: StoreProjectRequest::authorize() denies before the
         * validator or controller ever run. The app's exception Handler
         * converts that AuthorizationException into a redirect-back +
         * flash_message_warning, not a bare 403. */
        $response->assertRedirect(route('projects.create'));
        $response->assertSessionHas('flash_message_warning', 'This action is unauthorized.');
        $this->assertEquals($countBefore, Project::count());
    }
}
