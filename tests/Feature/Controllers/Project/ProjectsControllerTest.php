<?php

namespace Tests\Feature\Controllers\Project;

use App\Models\Client;
use App\Models\Permission;
use App\Models\Project;
use App\Models\Status;
use App\Models\User;
use Cache;
use DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

class ProjectsControllerTest extends AbstractTestCase
{
    use RefreshDatabase;

    private $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();

        $this->user   = User::factory()->create();
        $this->client = Client::factory()->create();
    }

    #[Test]
    #[Group('junie_repaired')]
    public function can_create_project()
    {
        /* Arrange */
        $permission = \App\Models\Permission::firstOrCreate([
            'name' => 'project-create',
        ], [
            'display_name' => 'Create project',
            'description'  => 'Permission to create a project',
            'grouping'     => 'project',
        ]);
        $role = $this->user->roles()->first() ?: \App\Models\Role::factory()->create();
        if ( ! $this->user->hasRole($role->name)) {
            $this->user->attachRole($role);
        }
        if ( ! $role->hasPermission('project-create')) {
            $role->attachPermission($permission);
        }
        Cache::tags('role_user')->flush();
        Cache::tags('permission_role')->flush();
        $this->user = $this->user->fresh();
        $this->actingAs($this->user);

        /* Act */
        $response = $this->json('POST', route('projects.store'), [
            'title'              => 'Project test',
            'description'        => 'This is a description',
            'status_id'          => Status::factory()->create(['source_type' => Project::class])->id,
            'user_assigned_id'   => $this->user->id,
            'user_created_id'    => $this->user->id,
            'client_external_id' => $this->client->external_id,
            'deadline'           => '2020-01-01',
        ]);

        /* Assert */
        $projects = Project::where('user_assigned_id', $this->user->id);
        $this->assertCount(1, $projects->get());
        $this->assertEquals($response->getData()->project_external_id, $projects->first()->external_id);
    }

    #[Test]
    public function it_can_update_assignee()
    {
        /* Arrange */
        $project = Project::factory()->create();
        $this->assertNotEquals($project->user_assigned_id, $this->user->id);
        $permission = Permission::firstOrCreate([
            'name' => 'can-assign-new-user-to-project',
        ], [
            'display_name' => 'Change assigned user',
            'description'  => 'Permission to change the assigned user on a project',
            'grouping'     => 'project',
        ]);
        $role = $this->user->roles()->first() ?: \App\Models\Role::factory()->create();
        if ( ! $this->user->hasRole($role->name)) {
            $this->user->attachRole($role);
        }
        if ( ! $role->hasPermission('can-assign-new-user-to-project')) {
            $role->attachPermission($permission);
        }
        Cache::tags('role_user')->flush();
        Cache::tags('permission_role')->flush();
        $this->user = $this->user->fresh();
        $this->actingAs($this->user);

        /* Act */
        $response = $this->json('PATCH', route('project.update.assignee', $project->external_id), [
            'user_assigned_id' => $this->user->id,
        ]);

        /* Assert */
        $this->assertEquals($project->refresh()->user_assigned_id, $this->user->id);
    }

    #[Test]
    public function it_can_update_status()
    {
        /* Arrange */
        $project = Project::factory()->create();
        $status  = Status::factory()->create(['source_type' => Project::class]);
        $permission = \App\Models\Permission::firstOrCreate([
            'name' => 'project-update-status',
        ], [
            'display_name' => 'Update project status',
            'description'  => 'Permission to update project status',
            'grouping'     => 'project',
        ]);
        $role = $this->user->roles()->first() ?: \App\Models\Role::factory()->create();
        if ( ! $this->user->hasRole($role->name)) {
            $this->user->attachRole($role);
        }
        if ( ! $role->hasPermission('project-update-status')) {
            $role->attachPermission($permission);
        }
        Cache::tags('role_user')->flush();
        Cache::tags('permission_role')->flush();
        $this->user = $this->user->fresh();
        $this->actingAs($this->user);
        $this->assertNotEquals($project->status_id, $status->id);

        /* Act */
        $response = $this->json('PATCH', route('project.update.status', $project->external_id), [
            'status_id' => $status->id,
        ]);

        /* Assert */
        $this->assertEquals($status->id, $project->refresh()->status_id);
    }

    #[Test]
    public function it_can_update_deadline_for_project()
    {
        /* Arrange */
        $this->withoutExceptionHandling();
        $project = Project::factory()->create();
        $role       = \App\Models\Role::factory()->create();
        $permission = \App\Models\Permission::firstOrCreate([
            'name' => 'project-update-deadline',
        ], [
            'display_name' => 'Change project deadline',
            'description'  => 'Permission to update a projects deadline',
            'grouping'     => 'project',
        ]);
        $role->attachPermission($permission);
        $this->user->attachRole($role);
        Cache::tags('role_user')->flush();
        Cache::tags('permission_role')->flush();
        $this->user = $this->user->fresh();
        $this->actingAs($this->user);

        /* Act */
        $response = $this->json('PATCH', route('project.update.deadline', $project->external_id), [
            'deadline_date' => '2020-08-06',
            'deadline_time' => '00:00',
        ]);

        /* Assert */
        $this->assertTrue($response->isRedirect(), 'Expected a redirect response');
        $this->assertFalse(session()->has('flash_message_warning'), 'Unexpected flash warning: ' . session('flash_message_warning'));
        $rawDeadline = DB::table('projects')->where('id', $project->id)->value('deadline');
        $expectedIso = \Carbon\Carbon::parse('2020-08-06 00:00:00')->toISOString();
        $this->assertEquals($expectedIso, \Carbon\Carbon::parse($rawDeadline)->toISOString(), 'Raw DB deadline mismatch');
        $this->assertEquals($expectedIso, $project->refresh()->deadline->toISOString());
    }
}
