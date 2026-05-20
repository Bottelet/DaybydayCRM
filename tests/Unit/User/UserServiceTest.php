<?php

namespace Tests\Unit\User;

use App\Enums\RoleType;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use App\Services\User\UserUpdateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

#[CoversClass(UserUpdateService::class)]
class UserServiceTest extends AbstractTestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_removes_password_fields_when_actor_cannot_change_password(): void
    {
        // Arrange
        $service          = new UserUpdateService();
        $unauthorizedUser = User::factory()->withRole(RoleType::USER->value)->create();
        $targetUser       = User::factory()->withRole(RoleType::USER->value)->create();

        // Act
        $payload = $service->prepareValidatedInput($unauthorizedUser, $targetUser, [
            'name'                  => 'Updated',
            'password'              => 'secret123',
            'password_confirmation' => 'secret123',
        ], null);

        // Assert
        $this->assertArrayHasKey('name', $payload);
        $this->assertArrayNotHasKey('password', $payload);
        $this->assertArrayNotHasKey('password_confirmation', $payload);
    }

    #[Test]
    public function it_hashes_password_when_actor_can_change_password(): void
    {
        // Arrange
        $service        = new UserUpdateService();
        $authorizedUser = User::factory()->withRole(RoleType::OWNER->value)->create();
        $targetUser     = User::factory()->withRole(RoleType::USER->value)->create();

        // Act
        $payload = $service->prepareValidatedInput($authorizedUser, $targetUser, [
            'password'              => 'secret123',
            'password_confirmation' => 'secret123',
        ], null);

        // Assert
        $this->assertTrue(Hash::check('secret123', $payload['password']));
    }

    #[Test]
    public function it_prevents_changing_last_owner_role(): void
    {
        // Arrange
        $service = new UserUpdateService();

        // Clear ALL existing owner role assignments to ensure test isolation
        // (seeder and AbstractTestCase may have assigned owner roles to other users)
        $ownerRole = Role::where('name', RoleType::OWNER->value)->first();
        if ($ownerRole) {
            DB::table('role_user')
                ->where('role_id', $ownerRole->id)
                ->delete();
        }

        $owner      = User::factory()->withRole(RoleType::OWNER->value)->create();
        $newRole    = Role::factory()->create(['name' => RoleType::USER->value, 'display_name' => 'User']);
        $department = Department::factory()->create();

        // Act
        $result = $service->syncRoleAndDepartment($owner, $owner, $newRole->id, $department->id);

        // Assert
        $this->assertFalse($result);
        $freshOwnerRole = $owner->fresh()->roles->first();
        $this->assertNotNull($freshOwnerRole);
        $this->assertSame(RoleType::OWNER->value, $freshOwnerRole->name);
    }
}
