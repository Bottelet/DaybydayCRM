<?php

namespace Tests\Unit\Entrust;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

class EntrustUserTraitPermissionTest extends AbstractTestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_returns_true_if_user_has_permission_via_role()
    {
        /* Arrange */
        $user       = User::factory()->create();
        $role       = Role::factory()->create();
        $permission = Permission::query()->firstOrCreate(['name' => 'client-create']);

        /* Act */
        $role->attachPermission($permission);
        $user->attachRole($role);

        /* Assert */
        $this->assertTrue($user->can('client-create'));
    }

    #[Test]
    public function it_returns_false_if_user_does_not_have_permission()
    {
        /* Arrange */
        $user = User::factory()->create();
        Permission::query()->firstOrCreate(['name' => 'client-create']);

        /* Act */
        $hasPermission = $user->can('client-create');

        /* Assert */
        $this->assertFalse($hasPermission);
    }

    #[Test]
    public function it_returns_true_if_user_has_permission_via_multiple_roles()
    {
        /* Arrange */
        $user       = User::factory()->create();
        $role1      = Role::factory()->create();
        $role2      = Role::factory()->create();
        $permission = Permission::query()->firstOrCreate(['name' => 'client-create']);

        /* Act */
        $role2->attachPermission($permission);
        $user->attachRole($role1);
        $user->attachRole($role2);

        /* Assert */
        $this->assertTrue($user->can('client-create'));
    }

    #[Test]
    public function it_returns_true_if_user_has_overlapping_permissions()
    {
        /* Arrange */
        $user       = User::factory()->create();
        $role1      = Role::factory()->create();
        $role2      = Role::factory()->create();
        $permission = Permission::query()->firstOrCreate(['name' => 'client-create']);

        /* Act */
        $role1->attachPermission($permission);
        $role2->attachPermission($permission);
        $user->attachRole($role1);
        $user->attachRole($role2);

        /* Assert */
        $this->assertTrue($user->can('client-create'));
    }

    #[Test]
    public function it_returns_false_if_user_has_roles_but_no_permissions()
    {
        /* Arrange */
        $user = User::factory()->create();
        $role = Role::factory()->create();

        /* Act */
        $user->attachRole($role);

        /* Assert */
        $this->assertFalse($user->can('client-create'));
    }

    #[Test]
    public function it_supports_wildcard_permission_checks()
    {
        /* Arrange */
        $user       = User::factory()->create();
        $role       = Role::factory()->create();
        $permission = Permission::query()->firstOrCreate(['name' => 'client-create']);

        /* Act */
        $role->attachPermission($permission);
        $user->attachRole($role);

        /* Assert */
        $this->assertTrue($user->can('client-*'));
    }

    #[Test]
    public function it_supports_array_input_for_permissions_require_all_false()
    {
        /* Arrange */
        $user  = User::factory()->create();
        $role  = Role::factory()->create();
        $perm1 = Permission::query()->firstOrCreate(['name' => 'client-create']);
        $perm2 = Permission::query()->firstOrCreate(['name' => 'client-edit']);

        /* Act */
        $role->attachPermission($perm1);
        $user->attachRole($role);

        /* Assert */
        $this->assertTrue($user->can(['client-create', 'client-edit'], false));
    }

    #[Test]
    public function it_supports_array_input_for_permissions_require_all_true()
    {
        /* Arrange */
        $user  = User::factory()->create();
        $role  = Role::factory()->create();
        $perm1 = Permission::query()->firstOrCreate(['name' => 'client-create']);
        $perm2 = Permission::query()->firstOrCreate(['name' => 'client-edit']);

        /* Act */
        $role->attachPermission($perm1);
        $role->attachPermission($perm2);
        $user->attachRole($role);

        /* Assert */
        $this->assertTrue($user->can(['client-create', 'client-edit'], true));
    }

    #[Test]
    public function it_returns_false_for_array_input_if_not_all_permissions_present_and_require_all_true()
    {
        /* Arrange */
        $user  = User::factory()->create();
        $role  = Role::factory()->create();
        $perm1 = Permission::query()->firstOrCreate(['name' => 'client-create']);

        /* Act */
        $role->attachPermission($perm1);
        $user->attachRole($role);

        /* Assert */
        $this->assertFalse($user->can(['client-create', 'client-edit'], true));
    }
}
