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
        $user = User::factory()->create();
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['name' => 'client-create']);
        $role->attachPermission($permission);
        $user->attachRole($role);
        $this->assertTrue($user->can('client-create'));
    }

    #[Test]
    public function it_returns_false_if_user_does_not_have_permission()
    {
        $user = User::factory()->create();
        $this->assertFalse($user->can('client-create'));
    }

    #[Test]
    public function it_returns_true_if_user_has_permission_via_multiple_roles()
    {
        $user = User::factory()->create();
        $role1 = Role::factory()->create();
        $role2 = Role::factory()->create();
        $permission = Permission::factory()->create(['name' => 'client-create']);
        $role2->attachPermission($permission);
        $user->attachRole($role1);
        $user->attachRole($role2);
        $this->assertTrue($user->can('client-create'));
    }

    #[Test]
    public function it_returns_true_if_user_has_overlapping_permissions()
    {
        $user = User::factory()->create();
        $role1 = Role::factory()->create();
        $role2 = Role::factory()->create();
        $permission = Permission::factory()->create(['name' => 'client-create']);
        $role1->attachPermission($permission);
        $role2->attachPermission($permission);
        $user->attachRole($role1);
        $user->attachRole($role2);
        $this->assertTrue($user->can('client-create'));
    }

    #[Test]
    public function it_returns_false_if_user_has_roles_but_no_permissions()
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();
        $user->attachRole($role);
        $this->assertFalse($user->can('client-create'));
    }

    #[Test]
    public function it_supports_wildcard_permission_checks()
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['name' => 'client-create']);
        $role->attachPermission($permission);
        $user->attachRole($role);
        $this->assertTrue($user->can('client-*'));
    }

    #[Test]
    public function it_supports_array_input_for_permissions_require_all_false()
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();
        $perm1 = Permission::factory()->create(['name' => 'client-create']);
        $perm2 = Permission::factory()->create(['name' => 'client-edit']);
        $role->attachPermission($perm1);
        $user->attachRole($role);
        $this->assertTrue($user->can(['client-create', 'client-edit'], false));
    }

    #[Test]
    public function it_supports_array_input_for_permissions_require_all_true()
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();
        $perm1 = Permission::factory()->create(['name' => 'client-create']);
        $perm2 = Permission::factory()->create(['name' => 'client-edit']);
        $role->attachPermission($perm1);
        $role->attachPermission($perm2);
        $user->attachRole($role);
        $this->assertTrue($user->can(['client-create', 'client-edit'], true));
    }

    #[Test]
    public function it_returns_false_for_array_input_if_not_all_permissions_present_and_require_all_true()
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();
        $perm1 = Permission::factory()->create(['name' => 'client-create']);
        $role->attachPermission($perm1);
        $user->attachRole($role);
        $this->assertFalse($user->can(['client-create', 'client-edit'], true));
    }
}

