<?php

namespace Tests\Unit\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

#[Group('user-role')]
class UserRoleTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    # region happy_path

    #[Test]
    public function it_factory_state_with_role_attaches_specified_role()
    {
        /** Arrange */
        // No additional arrangement needed

        /** Act */
        $user = User::factory()->withRole('employee')->create();

        /** Assert */
        $this->assertEquals(1, $user->roles->count());
        $this->assertEquals('employee', $user->roles->first()->name);
        $this->assertEquals('Employee', $user->roles->first()->display_name);
    }

    #[Test]
    public function it_factory_state_with_role_supports_different_roles()
    {
        /** Arrange */
        // No additional arrangement needed

        /** Act */
        $employee = User::factory()->withRole('employee')->create();
        $owner = User::factory()->withRole('owner')->create();
        $admin = User::factory()->withRole('administrator')->create();
        $manager = User::factory()->withRole('manager')->create();

        /** Assert */
        $this->assertEquals('employee', $employee->roles->first()->name);
        $this->assertEquals('owner', $owner->roles->first()->name);
        $this->assertEquals('administrator', $admin->roles->first()->name);
        $this->assertEquals('manager', $manager->roles->first()->name);
    }

    #[Test]
    public function it_user_role_relationship_is_accessible_when_using_factory_state()
    {
        /** Arrange */
        // No additional arrangement needed

        /** Act */
        $user = User::factory()->withRole('employee')->create();

        /** Assert */
        $this->assertNotNull($user->roles);
        $this->assertNotNull($user->roles->first());
        $this->assertNotNull($user->roles->first()->id);
        $this->assertNotNull($user->roles->first()->name);
        $this->assertNotNull($user->roles->first()->display_name);
    }

    # endregion

    # region edge_cases

    #[Test]
    public function it_users_created_without_role_state_have_no_roles_by_default()
    {
        /** Arrange */
        // No additional arrangement needed

        /** Act */
        $user = User::factory()->create();

        /** Assert */
        $this->assertTrue($user->roles->isEmpty(), 'User created without role state should have no roles');
    }

    #[Test]
    public function it_multiple_users_can_share_the_same_role_via_factory_state()
    {
        /** Arrange */
        // No additional arrangement needed

        /** Act */
        $user1 = User::factory()->withRole('employee')->create();
        $user2 = User::factory()->withRole('employee')->create();
        $user3 = User::factory()->withRole('employee')->create();

        /** Assert */
        $this->assertEquals('employee', $user1->roles->first()->name);
        $this->assertEquals('employee', $user2->roles->first()->name);
        $this->assertEquals('employee', $user3->roles->first()->name);

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

    # endregion
}
