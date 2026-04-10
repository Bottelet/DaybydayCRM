<?php

namespace Tests\Unit\Controllers\Task;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DeleteTaskControllerTest extends AbstractTestCase
{
    use RefreshDatabase;

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

        // Explicitly clear both permission caches
        Cache::tags('role_user')->flush();
        Cache::tags('permission_role')->flush();

        $this->actingAs($this->user);
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    #[Test]
    public function delete_task()
    {
        $response = $this->json('DELETE', route('tasks.destroy', $this->task->external_id));
        
        $response->assertStatus(200);
        $this->assertSoftDeleted('tasks', ['id' => $this->task->id]);
    }
}
