<?php

namespace Tests\Unit\Entrust;

use App\Models\Role;
use App\Models\User;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Exception;

/**
 * Tests for the EntrustUserTrait changes introduced in this PR:
 * - attachRole() now checks for existing roles before attaching (prevents duplicates)
 * - cachedRoles() now returns properly hydrated Eloquent models and filters non-objects
 */
#[Group('entrust')]
class EntrustUserTraitTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected $user;

    private Role $role;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->role = Role::where('name', 'owner')->first();
    }

    #[Test]
    public function attach_role_does_not_create_duplicate_role_assignment()
    {
        // Attach the role once
        $this->user->attachRole($this->role);

        // Count role_user entries before second attach
        $countBefore = $this->user->roles()->where('roles.id', $this->role->id)->count();

        // Attach the same role again — should be a no-op
        $this->user->attachRole($this->role);

        $countAfter = $this->user->roles()->where('roles.id', $this->role->id)->count();

        $this->assertEquals(1, $countBefore, 'User should have exactly 1 role assignment before second attach');
        $this->assertEquals(1, $countAfter, 'Duplicate attach should not create a second role_user entry');
    }

    #[Test]
    public function attach_role_accepts_role_object()
    {
        $user = User::factory()->create();
        $adminRole = Role::where('name', 'administrator')->first();

        $user->attachRole($adminRole);

        $this->assertTrue($user->hasRole('administrator'));
    }

    #[Test]
    public function attach_role_accepts_role_id()
    {
        $user = User::factory()->create();
        $adminRole = Role::where('name', 'administrator')->first();

        $user->attachRole($adminRole->id);

        $this->assertTrue($user->hasRole('administrator'));
    }

    #[Test]
    public function attach_role_accepts_role_array()
    {
        $user = User::factory()->create();
        $adminRole = Role::where('name', 'administrator')->first();

        $user->attachRole(['id' => $adminRole->id]);

        $this->assertTrue($user->hasRole('administrator'));
    }

    #[Test]
    public function attach_role_called_multiple_times_results_in_only_one_db_entry()
    {
        $user = User::factory()->create();
        $adminRole = Role::where('name', 'administrator')->first();

        // Call attachRole 3 times for the same role
        $user->attachRole($adminRole);
        $user->attachRole($adminRole);
        $user->attachRole($adminRole);

        $count = $user->roles()->where('roles.id', $adminRole->id)->count();
        $this->assertEquals(1, $count, 'Multiple attachRole calls should result in only one assignment');
    }

    #[Test]
    public function cached_roles_returns_eloquent_model_instances()
    {
        $cachedRoles = $this->user->cachedRoles();

        foreach ($cachedRoles as $role) {
            $this->assertIsObject($role, 'Each cached role should be an object');
            $this->assertInstanceOf(Role::class, $role, 'Each cached role should be a Role model instance');
        }
    }

    #[Test]
    public function cached_roles_returns_collection_with_correct_roles()
    {
        $user = User::factory()->create();
        $adminRole = Role::where('name', 'administrator')->first();
        $user->attachRole($adminRole);

        $cachedRoles = $user->cachedRoles();
        $roleNames = $cachedRoles->pluck('name')->toArray();

        $this->assertContains('administrator', $roleNames);
    }

    #[Test]
    public function cached_roles_returns_empty_when_no_roles_attached()
    {
        $user = User::factory()->create();
        // Factory doesn't attach roles by default, so user has no roles

        $cachedRoles = $user->cachedRoles();

        $this->assertCount(0, $cachedRoles);
    }

    #[Test]
    public function has_role_works_correctly_after_attach_role_fix()
    {
        $user = User::factory()->create();
        $adminRole = Role::where('name', 'administrator')->first();

        $this->assertFalse($user->hasRole('administrator'), 'User should not have role before attaching');

        $user->attachRole($adminRole);

        $this->assertTrue($user->hasRole('administrator'), 'User should have role after attaching');
    }

    #[Test]
    public function attaching_same_role_twice_does_not_throw_unique_constraint_exception()
    {
        $user = User::factory()->create();
        $adminRole = Role::where('name', 'administrator')->first();

        // This should not throw SQLSTATE[23000] Duplicate entry exception
        try {
            $user->attachRole($adminRole);
            $user->attachRole($adminRole);
            $this->assertTrue(true, 'No exception was thrown for duplicate attach');
        } catch (Exception $e) {
            $this->fail('attachRole threw an exception on duplicate: '.$e->getMessage());
        }
    }
}
