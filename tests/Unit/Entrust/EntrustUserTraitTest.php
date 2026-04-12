<?php

namespace Tests\Unit\Entrust;

use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

/**
 * Tests for the EntrustUserTrait changes introduced in this PR:
 * - attachRole() now checks for existing roles before attaching (prevents duplicates)
 * - cachedRoles() now returns properly hydrated Eloquent models and filters non-objects
 */
#[Group('entrust')]
class EntrustUserTraitTest extends AbstractTestCase
{
    use RefreshDatabase;

    /** @var User */
    protected $user;

    /** @var Role */
    private $role;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->role = Role::firstOrCreate(['name' => 'owner'], ['display_name' => 'Owner']);
    }

    // region happy_path

    #[Test]
    public function attach_role_accepts_role_object()
    {
        /** Arrange */
        $user = User::factory()->create();
        $adminRole = Role::firstOrCreate(['name' => 'administrator'], ['display_name' => 'Administrator']);

        /** Act */
        $user->attachRole($adminRole);

        /** Assert */
        $this->assertTrue($user->hasRole('administrator'));
    }

    #[Test]
    public function attach_role_accepts_role_id()
    {
        /** Arrange */
        $user = User::factory()->create();
        $adminRole = Role::firstOrCreate(['name' => 'administrator'], ['display_name' => 'Administrator']);

        /** Act */
        $user->attachRole($adminRole->id);

        /** Assert */
        $this->assertTrue($user->hasRole('administrator'));
    }

    #[Test]
    public function attach_role_accepts_role_array()
    {
        /** Arrange */
        $user = User::factory()->create();
        $adminRole = Role::firstOrCreate(['name' => 'administrator'], ['display_name' => 'Administrator']);

        /** Act */
        $user->attachRole(['id' => $adminRole->id]);

        /** Assert */
        $this->assertTrue($user->hasRole('administrator'));
    }

    #[Test]
    public function cached_roles_returns_eloquent_model_instances()
    {
        /** Arrange */
        // User already created in setUp()

        /** Act */
        $cachedRoles = $this->user->cachedRoles();

        /** Assert */
        foreach ($cachedRoles as $role) {
            $this->assertIsObject($role, 'Each cached role should be an object');
            $this->assertInstanceOf(Role::class, $role, 'Each cached role should be a Role model instance');
        }
    }

    #[Test]
    public function cached_roles_returns_collection_with_correct_roles()
    {
        /** Arrange */
        $user = User::factory()->create();
        $adminRole = Role::firstOrCreate(['name' => 'administrator'], ['display_name' => 'Administrator']);
        $user->attachRole($adminRole);

        /** Act */
        $cachedRoles = $user->cachedRoles();
        $roleNames = $cachedRoles->pluck('name')->toArray();

        /** Assert */
        $this->assertContains('administrator', $roleNames);
    }

    #[Test]
    public function has_role_works_correctly_after_attach_role_fix()
    {
        /** Arrange */
        $user = User::factory()->create();
        $adminRole = Role::firstOrCreate(['name' => 'administrator'], ['display_name' => 'Administrator']);

        /** Act & Assert */
        $this->assertFalse($user->hasRole('administrator'), 'User should not have role before attaching');

        $user->attachRole($adminRole);

        $this->assertTrue($user->hasRole('administrator'), 'User should have role after attaching');
    }

    // endregion

    // region edge_cases

    #[Test]
    public function attach_role_does_not_create_duplicate_role_assignment()
    {
        /** Arrange */
        $this->user->attachRole($this->role);
        $countBefore = $this->user->roles()->where('roles.id', $this->role->id)->count();

        /** Act */
        $this->user->attachRole($this->role);
        $countAfter = $this->user->roles()->where('roles.id', $this->role->id)->count();

        /** Assert */
        $this->assertEquals(1, $countBefore, 'User should have exactly 1 role assignment before second attach');
        $this->assertEquals(1, $countAfter, 'Duplicate attach should not create a second role_user entry');
    }

    #[Test]
    public function attach_role_called_multiple_times_results_in_only_one_db_entry()
    {
        /** Arrange */
        $user = User::factory()->create();
        $adminRole = Role::firstOrCreate(['name' => 'administrator'], ['display_name' => 'Administrator']);

        /** Act */
        $user->attachRole($adminRole);
        $user->attachRole($adminRole);
        $user->attachRole($adminRole);

        /** Assert */
        $count = $user->roles()->where('roles.id', $adminRole->id)->count();
        $this->assertEquals(1, $count, 'Multiple attachRole calls should result in only one assignment');
    }

    #[Test]
    public function cached_roles_returns_empty_when_no_roles_attached()
    {
        /** Arrange */
        $user = User::factory()->create();

        /** Act */
        $cachedRoles = $user->cachedRoles();

        /** Assert */
        $this->assertCount(0, $cachedRoles);
    }

    #[Test]
    public function attaching_same_role_twice_does_not_throw_unique_constraint_exception()
    {
        /** Arrange */
        $user = User::factory()->create();
        $role = Role::factory()->create();

        /** Act & Assert */
        try {
            $user->attachRole($role);
            $user->attachRole($role);
            $this->assertTrue(true, 'No exception was thrown for duplicate attach');
        } catch (Exception $e) {
            $this->fail('attachRole threw an exception on duplicate: '.$e->getMessage());
        }
    }

    // endregion
}
