<?php

namespace Tests\Feature\Controllers\Project;

use App\Models\Client;
use App\Models\Permission;
use App\Models\Project;
use App\Models\Status;
use App\Models\User;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Cache;
use DB;

class ProjectsControllerTest extends AbstractTestCase
{
    use RefreshDatabase;

    private $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();

        $this->user = User::factory()->create();
        $this->client = Client::factory()->create();
    }

    #[Test]
    #[Group('junie_repaired')]
    public function can_create_project()
    {

        // Grant permission to create a project
        $permission = \App\Models\Permission::firstOrCreate([
            'name' => 'project-create',
        ], [
            'display_name' => 'Create project',
            'description' => 'Permission to create a project',
            'grouping' => 'project',
        ]);
        $role = $this->user->roles()->first() ?: \App\Models\Role::factory()->create();
        if (! $this->user->hasRole($role->name)) {
            $this->user->attachRole($role);
        }
        if (! $role->hasPermission('project-create')) {
            $role->attachPermission($permission);
        }
        Cache::tags('role_user')->flush();
        Cache::tags('permission_role')->flush();
        $this->user = $this->user->fresh();
        $this->actingAs($this->user);

        $response = $this->json('POST', route('projects.store'), [
            'title' => 'Project test',
            'description' => 'This is a description',
            'status_id' => Status::factory()->create(['source_type' => Project::class])->id,
            'user_assigned_id' => $this->user->id,
            'user_created_id' => $this->user->id,
            'client_external_id' => $this->client->external_id,
            'deadline' => '2020-01-01',
        ]);

        $projects = Project::where('user_assigned_id', $this->user->id);

        $this->assertCount(1, $projects->get());
        $this->assertEquals($response->getData()->project_external_id, $projects->first()->external_id);
    }

    #[Test]
    public function it_can_update_assignee()
    {
        $project = Project::factory()->create();
        $this->assertNotEquals($project->user_assigned_id, $this->user->id);

        // Grant permission to assign new user to project
        $permission = Permission::firstOrCreate([
            'name' => 'can-assign-new-user-to-project',
        ], [
            'display_name' => 'Change assigned user',
            'description' => 'Permission to change the assigned user on a project',
            'grouping' => 'project',
        ]);

        $role = $this->user->roles()->first() ?: \App\Models\Role::factory()->create();
        if (! $this->user->hasRole($role->name)) {
            $this->user->attachRole($role);
        }
        if (! $role->hasPermission('can-assign-new-user-to-project')) {
            $role->attachPermission($permission);
        }

        Cache::tags('role_user')->flush();
        Cache::tags('permission_role')->flush();
        $this->user = $this->user->fresh();
        $this->actingAs($this->user);

        // Confirm permission is present for debugging (can be removed if not needed)
        // $this->assertTrue($this->user->can('can-assign-new-user-to-project'));

        $response = $this->json('PATCH', route('project.update.assignee', $project->external_id), [
            'user_assigned_id' => $this->user->id,
        ]);

        $this->assertEquals($project->refresh()->user_assigned_id, $this->user->id);
    }

    #[Test]
    public function it_can_update_status()
    {
        $project = Project::factory()->create();
        $status = Status::factory()->create(['source_type' => Project::class]);

        // Grant permission to update project status
        $permission = \App\Models\Permission::firstOrCreate([
            'name' => 'project-update-status',
        ], [
            'display_name' => 'Update project status',
            'description' => 'Permission to update project status',
            'grouping' => 'project',
        ]);
        $role = $this->user->roles()->first() ?: \App\Models\Role::factory()->create();
        if (! $this->user->hasRole($role->name)) {
            $this->user->attachRole($role);
        }
        if (! $role->hasPermission('project-update-status')) {
            $role->attachPermission($permission);
        }
        Cache::tags('role_user')->flush();
        Cache::tags('permission_role')->flush();
        $this->user = $this->user->fresh();
        $this->actingAs($this->user);

        $this->assertNotEquals($project->status_id, $status->id);

        $response = $this->json('PATCH', route('project.update.status', $project->external_id), [
            'status_id' => $status->id,
        ]);

        $this->assertEquals($status->id, $project->refresh()->status_id);
    }

    #[Test]
    public function it_can_update_deadline_for_project()
    {
        $this->withoutExceptionHandling();

        $project = Project::factory()->create();

        // Always create a new role and attach the permission
        $role = \App\Models\Role::factory()->create();
        $permission = \App\Models\Permission::firstOrCreate([
            'name' => 'project-update-deadline',
        ], [
            'display_name' => 'Change project deadline',
            'description' => 'Permission to update a projects deadline',
            'grouping' => 'project',
        ]);
        $role->attachPermission($permission);
        $this->user->attachRole($role);

        Cache::tags('role_user')->flush();
        Cache::tags('permission_role')->flush();
        $this->user = $this->user->fresh();
        $this->actingAs($this->user);

        $response = $this->json('PATCH', route('project.update.deadline', $project->external_id), [
            'deadline_date' => '2020-08-06',
            'deadline_time' => '00:00',
        ]);

        // Debug: Check for redirect or flash message
        $this->assertTrue($response->isRedirect(), 'Expected a redirect response');
        $this->assertFalse(session()->has('flash_message_warning'), 'Unexpected flash warning: '.session('flash_message_warning'));

        // Debug: Check the raw value in the database
        $rawDeadline = DB::table('projects')->where('id', $project->id)->value('deadline');
        $this->assertStringContainsString('2020-08-06', $rawDeadline, 'Raw DB deadline mismatch');

        $this->assertEquals('2020-08-06', $project->refresh()->deadline->format('Y-m-d'));
    }
}
