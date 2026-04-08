<?php

namespace Tests\Unit\Controllers\Task;

use App\Models\Client;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[Group('security')]
#[Group('assignment_authorization')]
class TaskAssignmentAuthorizationTest extends TestCase
{
    use DatabaseTransactions;

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
                'external_id' => \Str::uuid()->toString(),
            ]
        );

        // Create role with permission
        $authorizedRole = Role::firstOrCreate(
            ['name' => 'task-assigner'],
            [
                'display_name' => 'Task Assigner',
                'description' => 'Can assign tasks',
                'external_id' => \Str::uuid()->toString(),
            ]
        );
        $authorizedRole->perms()->sync([$permission->id]);

        // Create authorized user
        $this->authorizedUser = factory(User::class)->create();
        $this->authorizedUser->attachRole($authorizedRole);

        // Create unauthorized user (no permissions)
        $this->unauthorizedUser = factory(User::class)->create();

        // Create user to assign to
        $this->newAssignee = factory(User::class)->create();

        // Create task
        $client = factory(Client::class)->create();
        $this->task = factory(Task::class)->create([
            'user_assigned_id' => $this->authorizedUser->id,
            'client_id' => $client->id,
        ]);
    }

    #[Test]
    public function authorized_user_can_reassign_task()
    {
        $response = $this->actingAs($this->authorizedUser)
            ->patch(route('task.update.assignee', $this->task->external_id), [
                'user_assigned_id' => $this->newAssignee->id,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('flash_message');
        $this->assertEquals($this->newAssignee->id, $this->task->refresh()->user_assigned_id);
    }

    #[Test]
    public function unauthorized_user_cannot_reassign_task()
    {
        $originalAssignee = $this->task->user_assigned_id;

        $response = $this->actingAs($this->unauthorizedUser)
            ->patch(route('task.update.assignee', $this->task->external_id), [
                'user_assigned_id' => $this->newAssignee->id,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('flash_message_warning');
        $this->assertEquals($originalAssignee, $this->task->refresh()->user_assigned_id);
    }
}
