<?php

namespace Tests\Unit\Controllers\Task;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeleteTaskControllerTest extends TestCase
{
    use DatabaseTransactions;

    private $task;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->task = Task::factory()->create();

        $this->user = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'employee']);
        $permission = Permission::firstOrCreate(['name' => 'task-delete']);
        $role->attachPermission($permission);
        $this->user->attachRole($role);

        // Explicitly clear the permissions cache
        Cache::tags('role_user')->flush();

        $this->actingAs($this->user);
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    #[Test]
    public function delete_task()
    {
        $this->json('DELETE', route('tasks.destroy', $this->task->external_id));

        $this->assertSoftDeleted('tasks', ['id' => $this->task->id]);
    }
}
