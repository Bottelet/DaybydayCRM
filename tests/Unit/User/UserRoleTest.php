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
    public function a_user_cannot_exist_without_a_role()
    {
        // When creating a user via factory, it should automatically have a role attached
        $user = factory(User::class)->create();

        // Assert that the user has at least one role
        $this->assertFalse($user->roles->isEmpty(), 'User should have at least one role assigned');
        $this->assertGreaterThan(0, $user->roles->count(), 'User should have roles attached');
        
        // Verify the role is the default employee role
        $this->assertEquals('employee', $user->roles->first()->name);
    }

    #[Test]
    public function factory_created_user_has_default_employee_role()
    {
        $user = factory(User::class)->create();

        // User should have exactly one role (the default employee role)
        $this->assertEquals(1, $user->roles->count());
        $this->assertEquals('employee', $user->roles->first()->name);
        $this->assertEquals('Employee', $user->roles->first()->display_name);
    }

    #[Test]
    public function multiple_users_can_share_the_same_default_role()
    {
        // Create multiple users
        $user1 = factory(User::class)->create();
        $user2 = factory(User::class)->create();
        $user3 = factory(User::class)->create();

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
    public function user_role_relationship_is_accessible()
    {
        $user = factory(User::class)->create();

        // Test that we can access role properties via the relationship
        $this->assertNotNull($user->roles);
        $this->assertNotNull($user->roles->first());
        $this->assertNotNull($user->roles->first()->id);
        $this->assertNotNull($user->roles->first()->name);
        $this->assertNotNull($user->roles->first()->display_name);
    }
}
