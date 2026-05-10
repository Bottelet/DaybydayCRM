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

        $this->targetUser = User::factory()->withRole('employee')->create();
        $this->user = User::factory()->withRole('employee')->create();
        $this->actingAs($this->user);
        $this->unauthorizedUser = User::factory()->withRole('employee')->create();
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    #[Test]
    public function it_authorized_user_can_edit_user()
    {
        /* Arrange */
        $adminRole = Role::firstOrCreate(['name' => 'admin'], [
            'display_name' => 'Administrator',
            'description'  => 'Administrator role',
        ]);
        $permission = Permission::firstOrCreate(['name' => 'user-update']);
        $adminRole->attachPermission($permission);
        $this->user->attachRole($adminRole);
        Cache::tags('role_user')->flush();
        $this->user = $this->user->fresh();
        $this->actingAs($this->user);

        /* Act */
        $response = $this->json('GET', route('users.edit', $this->targetUser->external_id));

        /* Assert */
        $response->assertStatus(200);
    }

    #[Test]
    public function it_unauthorized_user_cannot_edit_user()
    {
        /* Arrange */
        $plainUser = User::factory()->withRole('employee')->create();
        $this->actingAs($plainUser);

        /* Act */
        $response = $this->json('GET', route('users.edit', $this->targetUser->external_id));

        /* Assert */
        $response->assertStatus(403);
    }

    #[Test]
    public function it_authorized_user_can_update_user()
    {
        /* Arrange */
        $adminRole = Role::firstOrCreate(['name' => 'admin'], [
            'display_name' => 'Administrator',
            'description'  => 'Administrator role',
        ]);
        $permission = Permission::firstOrCreate(['name' => 'user-update']);
        $adminRole->attachPermission($permission);
        $this->user->attachRole($adminRole);
        Cache::tags('role_user')->flush();
        $this->user = $this->user->fresh();
        $this->actingAs($this->user);

        /* Act */
        $response = $this->json('PATCH', route('users.update', $this->targetUser->external_id), [
            'name'        => 'Updated Name',
            'email'       => $this->targetUser->email,
            'departments' => $this->targetUser->department()->first()->id,
            'roles'       => $this->targetUser->roles->first()->id,
        ]);

        /* Assert */
        $response->assertStatus(302);
    }

    #[Test]
    public function it_unauthorized_user_cannot_update_user()
    {
        /* Arrange */
        $this->actingAs($this->unauthorizedUser);
        $originalName = $this->targetUser->name;

        /* Act */
        $response = $this->json('PATCH', route('users.update', $this->targetUser->external_id), [
            'name'        => 'Hacked Name',
            'email'       => $this->targetUser->email,
            'departments' => $this->targetUser->department()->first()->id,
            'roles'       => $this->targetUser->roles->first()->id,
        ]);

        /* Assert */
        $response->assertStatus(403);
        $this->assertEquals($originalName, $this->targetUser->refresh()->name);
    }

    #[Test]
    public function it_user_update_prevents_password_change_without_permission()
    {
        /* Arrange */
        $manager = User::factory()->create();
        $managerRole = Role::firstOrCreate(
            ['name' => 'manager'],
            [
                'display_name' => 'Manager',
                'description'  => 'Manager role',
                'external_id'  => Str::uuid()->toString(),
            ]
        );
        $manager->attachRole($managerRole);
        $permission = Permission::firstOrCreate(['name' => 'user-update']);
        $managerRole->attachPermission($permission);
        $manager = $manager->fresh();
        $this->actingAs($manager);
        $originalPassword = $this->targetUser->password;

        /* Act */
        $response = $this->json('PATCH', route('users.update', $this->targetUser->external_id), [
            'name'        => $this->targetUser->name,
            'email'       => $this->targetUser->email,
            'password'    => 'newpassword123',
            'departments' => $this->targetUser->department()->first()->id,
            'roles'       => $this->targetUser->roles->first()->id,
        ]);

        /* Assert */
        $this->assertEquals($originalPassword, $this->targetUser->refresh()->password);
    }
}
