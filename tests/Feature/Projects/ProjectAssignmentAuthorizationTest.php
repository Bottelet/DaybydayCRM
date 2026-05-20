<?php

namespace Tests\Feature\Projects;

use App\Models\Client;
use App\Models\Permission;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

#[Group('security')]
#[Group('assignment_authorization')]
class ProjectAssignmentAuthorizationTest extends AbstractTestCase
{
    use RefreshDatabase;

    private User $authorizedUser;

    private User $unauthorizedUser;

    private User $newAssignee;

    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        /* Arrange */
        $permission = Permission::query()->firstOrCreate(
            ['name' => 'can-assign-new-user-to-project'],
            [
                'display_name' => 'Assign users to projects',
                'description'  => 'Can assign users to projects',
                'external_id'  => Str::uuid()->toString(),
            ]
        );
        $authorizedRole = Role::query()->firstOrCreate(
            ['name' => 'project-assigner'],
            [
                'display_name' => 'Projects Assigner',
                'description'  => 'Can assign projects',
                'external_id'  => Str::uuid()->toString(),
            ]
        );
        $authorizedRole->perms()->sync([$permission->id]);
        $this->authorizedUser = User::factory()->create();
        $this->authorizedUser->attachRole($authorizedRole);
        $this->unauthorizedUser = User::factory()->create();
        $this->newAssignee      = User::factory()->create();
        $client                 = Client::factory()->create();
        $this->project          = Project::factory()->create([
            'user_assigned_id' => $this->authorizedUser->id,
            'client_id'        => $client->id,
        ]);
    }

    #[Test]
    public function it_authorized_user_can_reassign_project()
    {
        /* Arrange */
        $originalAssignee = $this->project->user_assigned_id;
        \Illuminate\Support\Facades\Cache::tags('role_user')->flush();
        $this->authorizedUser = $this->authorizedUser->fresh();
        $this->assertTrue($this->authorizedUser->can('can-assign-new-user-to-project'));
        $this->assertEquals($this->authorizedUser->id, $originalAssignee);

        /* Act */
        $response = $this->actingAs($this->authorizedUser)
            ->patch(route('project.update.assignee', $this->project->external_id), [
                'user_assigned_id' => $this->newAssignee->id,
            ]);

        /* Assert */
        $response->assertRedirect();
        $response->assertSessionHas('flash_message');
        $this->assertDatabaseHas('projects', [
            'id'               => $this->project->id,
            'user_assigned_id' => $this->newAssignee->id,
        ]);
        $this->assertEquals($this->newAssignee->id, $this->project->refresh()->user_assigned_id);
    }

    #[Test]
    public function it_unauthorized_user_cannot_reassign_project()
    {
        /* Arrange */
        $originalAssignee = $this->project->user_assigned_id;
        \Illuminate\Support\Facades\Cache::tags('role_user')->flush();
        $this->unauthorizedUser = $this->unauthorizedUser->fresh();
        $this->assertFalse($this->unauthorizedUser->can('can-assign-new-user-to-project'));

        /* Act */
        $response = $this->actingAs($this->unauthorizedUser)
            ->patch(route('project.update.assignee', $this->project->external_id), [
                'user_assigned_id' => $this->newAssignee->id,
            ]);

        /* Assert */
        $response->assertRedirect();
        $response->assertSessionHas('flash_message_warning', __('You do not have permission to assign users to this project'));
        $this->assertDatabaseHas('projects', [
            'id'               => $this->project->id,
            'user_assigned_id' => $originalAssignee,
        ]);
        $this->assertEquals($originalAssignee, $this->project->refresh()->user_assigned_id);
    }
}
