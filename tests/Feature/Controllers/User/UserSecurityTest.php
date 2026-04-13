<?php

namespace Tests\Feature\Controllers\User;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Cache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

#[Group('security')]
#[Group('user-controller')]
class UserSecurityTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected $targetUser;

    protected $unauthorizedUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create target user with employee role for testing
        $this->targetUser = User::factory()->withRole('employee')->create();

        // Create and authenticate a user with employee role
        $this->user = User::factory()->withRole('employee')->create();
        $this->actingAs($this->user);

        // Create a user without user-update permission
        $this->unauthorizedUser = User::factory()->withRole('employee')->create();

        // Disable CSRF middleware for all tests
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    #[Test]
    public function it_authorized_user_can_edit_user()
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin'], [
            'display_name' => 'Administrator',
            'description'  => 'Administrator role',
        ]);
        // Ensure the admin role has the user-update permission
        $permission = Permission::firstOrCreate(['name' => 'user-update']);
        $adminRole->attachPermission($permission);
        $this->user->attachRole($adminRole);
        Cache::tags('role_user')->flush();
        $this->user = $this->user->fresh();
        $this->actingAs($this->user);

        $response = $this->json('GET', route('users.edit', $this->targetUser->external_id));
        // ...existing code...

        $response->assertStatus(200);
    }

    #[Test]
    public function it_unauthorized_user_cannot_edit_user()
    {
        // Use a user that truly has no update permission
        $plainUser = User::factory()->withRole('employee')->create();
        $this->actingAs($plainUser);

        $response = $this->json('GET', route('users.edit', $this->targetUser->external_id));

        $response->assertStatus(403);
    }

    #[Test]
    public function it_authorized_user_can_update_user()
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin'], [
            'display_name' => 'Administrator',
            'description'  => 'Administrator role',
        ]);
        // Ensure the admin role has the user-update permission
        $permission = Permission::firstOrCreate(['name' => 'user-update']);
        $adminRole->attachPermission($permission);
        $this->user->attachRole($adminRole);
        Cache::tags('role_user')->flush();
        $this->user = $this->user->fresh();
        $this->actingAs($this->user);

        $response = $this->json('PATCH', route('users.update', $this->targetUser->external_id), [
            'name'        => 'Updated Name',
            'email'       => $this->targetUser->email,
            'departments' => $this->targetUser->department()->first()->id,
            'roles'       => $this->targetUser->roles->first()->id,
        ]);

        $response->assertStatus(302);
    }

    #[Test]
    public function it_unauthorized_user_cannot_update_user()
    {
        $this->actingAs($this->unauthorizedUser);

        $originalName = $this->targetUser->name;

        $response = $this->json('PATCH', route('users.update', $this->targetUser->external_id), [
            'name'        => 'Hacked Name',
            'email'       => $this->targetUser->email,
            'departments' => $this->targetUser->department()->first()->id,
            'roles'       => $this->targetUser->roles->first()->id,
        ]);

        $response->assertStatus(403);
        $this->assertEquals($originalName, $this->targetUser->refresh()->name);
    }

    #[Test]
    public function it_user_update_prevents_password_change_without_permission()
    {
        // Test that non-owners can't change passwords of other users
        $manager = User::factory()->create();

        // Create or get manager role
        $managerRole = Role::firstOrCreate(
            ['name' => 'manager'],
            [
                'display_name' => 'Manager',
                'description'  => 'Manager role',
                'external_id'  => Str::uuid()->toString(),
            ]
        );
        $manager->attachRole($managerRole);

        // Add user-update permission to manager
        $permission = Permission::firstOrCreate(['name' => 'user-update']);
        $managerRole->attachPermission($permission);

        $this->actingAs($manager);

        $originalPassword = $this->targetUser->password;

        $response = $this->json('PATCH', route('users.update', $this->targetUser->external_id), [
            'name'        => $this->targetUser->name,
            'email'       => $this->targetUser->email,
            'password'    => 'newpassword123',
            'departments' => $this->targetUser->department()->first()->id,
            'roles'       => $this->targetUser->roles->first()->id,
        ]);

        // Password should not be changed if user doesn't have permission
        $this->assertEquals($originalPassword, $this->targetUser->refresh()->password);
    }
}
