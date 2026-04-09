<?php

namespace Tests\Unit\Controllers\User;

use App\Models\Department;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[Group('security')]
#[Group('user-controller')]
class UserSecurityTest extends TestCase
{
    use DatabaseTransactions;

    protected $targetUser;

    protected $unauthorizedUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure at least one department exists for the factory afterCreating callback
        if (Department::count() === 0) {
            factory(Department::class)->create();
        }

        // Ensure a default role exists
        $defaultRole = Role::firstOrCreate(
            ['name' => 'employee'],
            [
                'display_name' => 'Employee',
                'description' => 'Default employee role',
                'external_id' => \Illuminate\Support\Str::uuid()->toString(),
            ]
        );

        $this->targetUser = factory(User::class)->create();
        // Ensure targetUser has a role
        if ($this->targetUser->roles->isEmpty()) {
            $this->targetUser->attachRole($defaultRole);
        }

        // Create a user without user-update permission
        $this->unauthorizedUser = factory(User::class)->create();
        $role = Role::where('name', 'employee')->first();
        $this->unauthorizedUser->attachRole($role);
    }

    #[Test]
    public function authorized_user_can_edit_user()
    {
        $response = $this->json('GET', route('users.edit', $this->targetUser->external_id));

        $response->assertStatus(200);
    }

    #[Test]
    public function unauthorized_user_cannot_edit_user()
    {
        $this->actingAs($this->unauthorizedUser);

        $response = $this->json('GET', route('users.edit', $this->targetUser->external_id));

        $response->assertStatus(403);
    }

    #[Test]
    public function authorized_user_can_update_user()
    {
        $response = $this->json('PATCH', route('users.update', $this->targetUser->external_id), [
            'name' => 'Updated Name',
            'email' => $this->targetUser->email,
            'departments' => $this->targetUser->department()->first()->id,
            'roles' => $this->targetUser->roles->first()->id,
        ]);

        $response->assertRedirect();
        $this->assertEquals('Updated Name', $this->targetUser->refresh()->name);
    }

    #[Test]
    public function unauthorized_user_cannot_update_user()
    {
        $this->actingAs($this->unauthorizedUser);

        $originalName = $this->targetUser->name;

        $response = $this->json('PATCH', route('users.update', $this->targetUser->external_id), [
            'name' => 'Hacked Name',
            'email' => $this->targetUser->email,
            'departments' => $this->targetUser->department()->first()->id,
            'roles' => $this->targetUser->roles->first()->id,
        ]);

        $response->assertStatus(403);
        $this->assertEquals($originalName, $this->targetUser->refresh()->name);
    }

    #[Test]
    public function user_update_prevents_password_change_without_permission()
    {
        // Test that non-owners can't change passwords of other users
        $manager = factory(User::class)->create();
        $managerRole = Role::where('name', 'manager')->first();
        $manager->attachRole($managerRole);

        // Add user-update permission to manager
        $permission = Permission::firstOrCreate(['name' => 'user-update']);
        $managerRole->attachPermission($permission);

        $this->actingAs($manager);

        $originalPassword = $this->targetUser->password;

        $response = $this->json('PATCH', route('users.update', $this->targetUser->external_id), [
            'name' => $this->targetUser->name,
            'email' => $this->targetUser->email,
            'password' => 'newpassword123',
            'departments' => $this->targetUser->department()->first()->id,
            'roles' => $this->targetUser->roles->first()->id,
        ]);

        // Password should not be changed if user doesn't have permission
        $this->assertEquals($originalPassword, $this->targetUser->refresh()->password);
    }
}
