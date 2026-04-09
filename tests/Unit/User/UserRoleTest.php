<?php

namespace Tests\Unit\User;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[Group('user-role')]
class UserRoleTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function users_created_without_role_state_have_no_roles_by_default()
    {
        // When creating a user via factory without the 'withRole' state, it should NOT have roles
        $user = factory(User::class)->create();

        // Assert that the user has no roles by default
        $this->assertTrue($user->roles->isEmpty(), 'User created without role state should have no roles');
    }

    #[Test]
    public function factory_state_with_role_attaches_specified_role()
    {
        // When using the 'withRole' state with 'employee', user should have employee role
        $user = factory(User::class)->state('withRole', ['role' => 'employee'])->create();

        $this->assertEquals(1, $user->roles->count());
        $this->assertEquals('employee', $user->roles->first()->name);
        $this->assertEquals('Employee', $user->roles->first()->display_name);
    }

    #[Test]
    public function factory_state_with_role_supports_different_roles()
    {
        // Test employee role
        $employee = factory(User::class)->state('withRole', ['role' => 'employee'])->create();
        $this->assertEquals('employee', $employee->roles->first()->name);

        // Test owner role
        $owner = factory(User::class)->state('withRole', ['role' => 'owner'])->create();
        $this->assertEquals('owner', $owner->roles->first()->name);

        // Test administrator role
        $admin = factory(User::class)->state('withRole', ['role' => 'administrator'])->create();
        $this->assertEquals('administrator', $admin->roles->first()->name);

        // Test manager role
        $manager = factory(User::class)->state('withRole', ['role' => 'manager'])->create();
        $this->assertEquals('manager', $manager->roles->first()->name);
    }

    #[Test]
    public function multiple_users_can_share_the_same_role_via_factory_state()
    {
        // Create multiple users with employee role
        $user1 = factory(User::class)->state('withRole', ['role' => 'employee'])->create();
        $user2 = factory(User::class)->state('withRole', ['role' => 'employee'])->create();
        $user3 = factory(User::class)->state('withRole', ['role' => 'employee'])->create();

        // All should have the employee role
        $this->assertEquals('employee', $user1->roles->first()->name);
        $this->assertEquals('employee', $user2->roles->first()->name);
        $this->assertEquals('employee', $user3->roles->first()->name);

        // They should all reference the same role instance (same ID)
        $this->assertEquals(
            $user1->roles->first()->id,
            $user2->roles->first()->id,
            'All users should share the same employee role instance'
        );
        $this->assertEquals(
            $user2->roles->first()->id,
            $user3->roles->first()->id,
            'All users should share the same employee role instance'
        );
    }

    #[Test]
    public function user_role_relationship_is_accessible_when_using_factory_state()
    {
        $user = factory(User::class)->state('withRole', ['role' => 'employee'])->create();

        // Test that we can access role properties via the relationship
        $this->assertNotNull($user->roles);
        $this->assertNotNull($user->roles->first());
        $this->assertNotNull($user->roles->first()->id);
        $this->assertNotNull($user->roles->first()->name);
        $this->assertNotNull($user->roles->first()->display_name);
    }
}
