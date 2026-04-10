<?php

namespace Tests\Unit\Controllers\Task;

use App\Models\Client;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

#[Group('security')]
#[Group('assignment_authorization')]
class TaskAssignmentAuthorizationAbstractTest extends AbstractTestCase
{
    use RefreshDatabase;

    private User $authorizedUser;

    private User $unauthorizedUser;

    private User $newAssignee;

    private Task $task;

    protected function setUp(): void
    {
        parent::setUp();

        // Create permission
        $permission = Permission::firstOrCreate(
            ['name' => 'can-assign-new-user-to-task'],
            [
                'display_name' => 'Assign users to tasks',
                'description' => 'Can assign users to tasks',
                'external_id' => Str::uuid()->toString(),
            ]
        );

        // Create role with permission
        $authorizedRole = Role::firstOrCreate(
            ['name' => 'task-assigner'],
            [
                'display_name' => 'Task Assigner',
                'description' => 'Can assign tasks',
                'external_id' => Str::uuid()->toString(),
            ]
        );
        $authorizedRole->perms()->sync([$permission->id]);

        // Create authorized user
        $this->authorizedUser = User::factory()->create();
        $this->authorizedUser->attachRole($authorizedRole);

        // Create unauthorized user (no permissions)
        $this->unauthorizedUser = User::factory()->create();

        // Create user to assign to
        $this->newAssignee = User::factory()->create();

        // Create task
        $client = Client::factory()->create();
        $this->task = Task::factory()->create([
            'user_assigned_id' => $this->authorizedUser->id,
            'client_id' => $client->id,
        ]);

        // Explicitly clear the permissions cache
        Cache::tags('role_user')->flush();
    }

    #[Test]
    public function authorized_user_can_reassign_task()
    {
        $originalAssignee = $this->task->user_assigned_id;

        // Verify the authorized user has the permission
        $this->assertTrue($this->authorizedUser->can('can-assign-new-user-to-task'));

        // Verify initial state and prevent false positives
        // Ensure we're actually changing the assignment (not reassigning to same user)
        $this->assertEquals($this->authorizedUser->id, $originalAssignee);
        $this->assertNotEquals($this->newAssignee->id, $originalAssignee);

        $response = $this->actingAs($this->authorizedUser)
            ->patch(route('task.update.assignee', $this->task->external_id), [
                'user_assigned_id' => $this->newAssignee->id,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('flash_message');

        // Verify assignment was updated in database
        $this->assertDatabaseHas('tasks', [
            'id' => $this->task->id,
            'user_assigned_id' => $this->newAssignee->id,
        ]);
        $this->assertEquals($this->newAssignee->id, $this->task->refresh()->user_assigned_id);
    }

    #[Test]
    public function unauthorized_user_cannot_reassign_task()
    {
        $originalAssignee = $this->task->user_assigned_id;

        // Verify the unauthorized user does NOT have the permission
        $this->assertFalse($this->unauthorizedUser->can('can-assign-new-user-to-task'));

        // Verify initial state
        $this->assertEquals($this->authorizedUser->id, $originalAssignee);

        $response = $this->actingAs($this->unauthorizedUser)
            ->patch(route('task.update.assignee', $this->task->external_id), [
                'user_assigned_id' => $this->newAssignee->id,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('flash_message_warning');

        // Verify assignment was NOT changed in database
        $this->assertDatabaseHas('tasks', [
            'id' => $this->task->id,
            'user_assigned_id' => $originalAssignee,
        ]);
        $this->assertEquals($originalAssignee, $this->task->refresh()->user_assigned_id);
    }
}
