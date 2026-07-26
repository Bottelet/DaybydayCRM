<?php

namespace Tests\Feature\Tasks;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

class DeleteTaskControllerTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected $user;

    private $task;

    protected function setUp(): void
    {
        parent::setUp();

        $this->task = Task::factory()->create();

        $this->user = User::factory()->create();
        $role       = Role::query()->firstOrCreate(['name' => 'employee']);
        $permission = Permission::query()->firstOrCreate(['name' => 'task-delete']);
        $role->attachPermission($permission);
        $this->user->attachRole($role);

        Cache::tags('role_user')->flush();
        Cache::tags('permission_role')->flush();
        $this->user = $this->user->fresh();

        $this->actingAs($this->user);
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    #[Test]
    public function it_deletes_task()
    {
        /* Arrange */
        $this->actingAs($this->user);

        /* Act */
        $response = $this->delete(route('tasks.destroy', $this->task->external_id), [], ['Accept' => 'application/json']);

        /* Assert */
        $response->assertStatus(200);
        $this->assertSoftDeleted('tasks', ['id' => $this->task->id]);
    }

    #[Test]
    public function it_rejects_deletion_when_user_lacks_permission()
    {
        /* Arrange */
        $unauthorizedUser = User::factory()->create();
        $this->actingAs($unauthorizedUser);

        /* Act */
        $response = $this->delete(route('tasks.destroy', $this->task->external_id), [], ['Accept' => 'application/json']);

        /* Assert */
        $response->assertStatus(403);
        $this->assertNotSoftDeleted('tasks', ['id' => $this->task->id]);
    }
}
