<?php

namespace Tests\Unit\Controllers\Project;

use App\Models\Client;
use App\Models\Permission;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[Group('security')]
#[Group('assignment_authorization')]
class ProjectAssignmentAuthorizationTest extends TestCase
{
    use DatabaseTransactions;

    private User $authorizedUser;
    private User $unauthorizedUser;
    private User $newAssignee;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        // Create permission
        $permission = Permission::firstOrCreate(
            ['name' => 'can-assign-new-user-to-project'],
            [
                'display_name' => 'Assign users to projects',
                'description' => 'Can assign users to projects',
                'external_id' => \Str::uuid()->toString(),
            ]
        );

        // Create role with permission
        $authorizedRole = Role::firstOrCreate(
            ['name' => 'project-assigner'],
            [
                'display_name' => 'Project Assigner',
                'description' => 'Can assign projects',
                'external_id' => \Str::uuid()->toString(),
            ]
        );
        $authorizedRole->perms()->sync([$permission->id]);

        // Create authorized user
        $this->authorizedUser = factory(User::class)->create();
        $this->authorizedUser->attachRole($authorizedRole);

        // Create unauthorized user
        $this->unauthorizedUser = factory(User::class)->create();

        // Create user to assign to
        $this->newAssignee = factory(User::class)->create();

        // Create project
        $client = factory(Client::class)->create();
        $this->project = factory(Project::class)->create([
            'user_assigned_id' => $this->authorizedUser->id,
            'client_id' => $client->id,
        ]);
    }

    #[Test]
    public function authorized_user_can_reassign_project()
    {
        $originalAssignee = $this->project->user_assigned_id;
        
        // Verify the authorized user has the permission
        $this->assertTrue($this->authorizedUser->can('can-assign-new-user-to-project'));
        
        // Verify initial state
        $this->assertEquals($this->authorizedUser->id, $originalAssignee);

        $response = $this->actingAs($this->authorizedUser)
            ->patch(route('project.update.assignee', $this->project->external_id), [
                'user_assigned_id' => $this->newAssignee->id,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('flash_message');
        
        // Verify assignment was updated in database
        $this->assertDatabaseHas('projects', [
            'id' => $this->project->id,
            'user_assigned_id' => $this->newAssignee->id,
        ]);
        $this->assertEquals($this->newAssignee->id, $this->project->refresh()->user_assigned_id);
    }

    #[Test]
    public function unauthorized_user_cannot_reassign_project()
    {
        $originalAssignee = $this->project->user_assigned_id;
        
        // Verify the unauthorized user does NOT have the permission
        $this->assertFalse($this->unauthorizedUser->can('can-assign-new-user-to-project'));

        $response = $this->actingAs($this->unauthorizedUser)
            ->patch(route('project.update.assignee', $this->project->external_id), [
                'user_assigned_id' => $this->newAssignee->id,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('flash_message_warning', __('You do not have permission to assign users to this project'));
        
        // Verify assignment was NOT changed in database
        $this->assertDatabaseHas('projects', [
            'id' => $this->project->id,
            'user_assigned_id' => $originalAssignee,
        ]);
        $this->assertEquals($originalAssignee, $this->project->refresh()->user_assigned_id);
    }
}
