<?php

namespace Tests\Feature\Tasks;

use App\Models\Client;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

#[Group('security')]
#[Group('assignment_authorization')]
class TaskAssignmentAuthorizationTest extends AbstractTestCase
{
    use RefreshDatabase;

    private User $authorizedUser;

    private User $unauthorizedUser;

    private User $newAssignee;

    private Task $task;

    protected function setUp(): void
    {
        parent::setUp();

        $permission = Permission::query()->firstOrCreate(
            ['name' => 'can-assign-new-user-to-task'],
            [
                'display_name' => 'Assign users to tasks',
                'description'  => 'Can assign users to tasks',
                'external_id'  => Str::uuid()->toString(),
            ]
        );

        $authorizedRole = Role::query()->firstOrCreate(
            ['name' => 'task-assigner'],
            [
                'display_name' => 'Tasks Assigner',
                'description'  => 'Can assign tasks',
                'external_id'  => Str::uuid()->toString(),
            ]
        );
        $authorizedRole->perms()->sync([$permission->id]);

        $this->authorizedUser = User::factory()->create();
        $this->authorizedUser->attachRole($authorizedRole);
        // Flush cache and reload user to ensure permissions are visible
        Cache::flush();
        $this->authorizedUser = $this->authorizedUser->fresh();

        $this->unauthorizedUser = User::factory()->create();

        $this->newAssignee = User::factory()->create();

        $client     = Client::factory()->create();
        $this->task = Task::factory()->create([
            'user_assigned_id' => $this->authorizedUser->id,
            'client_id'        => $client->id,
        ]);

        Cache::tags('role_user')->flush();
    }

    #[Test]
    public function it_authorized_user_can_reassign_task()
    {
        /* Arrange */
        $originalAssignee = $this->task->user_assigned_id;

        $this->assertTrue($this->authorizedUser->can('can-assign-new-user-to-task'));

        $this->assertEquals($this->authorizedUser->id, $originalAssignee);
        $this->assertNotEquals($this->newAssignee->id, $originalAssignee);

        /* Act */
        $response = $this->actingAs($this->authorizedUser)
            ->patch(route('task.update.assignee', $this->task->external_id), [
                'user_assigned_id' => $this->newAssignee->id,
            ]);

        /* Assert */
        $response->assertRedirect();
        $response->assertSessionHas('flash_message');

        $this->assertDatabaseHas('tasks', [
            'id'               => $this->task->id,
            'user_assigned_id' => $this->newAssignee->id,
        ]);
        $this->assertEquals($this->newAssignee->id, $this->task->refresh()->user_assigned_id);
    }

    #[Test]
    public function it_unauthorized_user_cannot_reassign_task()
    {
        /* Arrange */
        $originalAssignee = $this->task->user_assigned_id;

        $this->assertFalse($this->unauthorizedUser->can('can-assign-new-user-to-task'));

        $this->assertEquals($this->authorizedUser->id, $originalAssignee);

        /* Act */
        $response = $this->actingAs($this->unauthorizedUser)
            ->patch(route('task.update.assignee', $this->task->external_id), [
                'user_assigned_id' => $this->newAssignee->id,
            ]);

        /* Assert */
        $response->assertRedirect();
        $response->assertSessionHas('flash_message_warning');

        $this->assertDatabaseHas('tasks', [
            'id'               => $this->task->id,
            'user_assigned_id' => $originalAssignee,
        ]);
        $this->assertEquals($originalAssignee, $this->task->refresh()->user_assigned_id);
    }
}
