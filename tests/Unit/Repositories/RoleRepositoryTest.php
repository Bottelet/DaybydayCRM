<?php

namespace Tests\Unit\Repositories;

use App\Models\Role;
use App\Repositories\Role\RoleRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

/**
 * Tests for RoleRepository::allRoles() after the syntax fix.
 * The fix changed Role::all('display_name', 'id', 'name', 'external_id')
 * to Role::all(['display_name', 'id', 'name', 'external_id']),
 * which is the correct Eloquent syntax for selecting specific columns.
 */
#[Group('repository')]
class RoleRepositoryTest extends AbstractTestCase
{
    use RefreshDatabase;

    /** @var RoleRepository */
    private $repository;

    protected function setUp(): void
    {
        parent::setUp();

        Role::factory()->create(['name' => 'administrator', 'display_name' => 'Administrator']);
        Role::factory()->create(['name' => 'manager', 'display_name' => 'Manager']);
        Role::factory()->create(['name' => 'employee', 'display_name' => 'Employee']);

        $this->repository = new RoleRepository();
    }

    # region happy_path

    #[Test]
    public function it_all_roles_excludes_owner_role()
    {
        /** Arrange */
        // Already arranged in setUp()

        /** Act */
        $roles = $this->repository->allRoles();
        $roleNames = $roles->pluck('name')->toArray();

        /** Assert */
        $this->assertNotContains('owner', $roleNames, 'allRoles() should not include the owner role');
    }

    #[Test]
    public function it_all_roles_returns_roles_with_required_columns()
    {
        /** Arrange */
        // Already arranged in setUp()

        /** Act */
        $roles = $this->repository->allRoles();

        /** Assert */
        $this->assertNotEmpty($roles, 'allRoles() should return at least one role');

        foreach ($roles as $role) {
            $this->assertNotNull($role->display_name, 'Role should have display_name');
            $this->assertNotNull($role->id, 'Role should have id');
            $this->assertNotNull($role->name, 'Role should have name');
        }
    }

    #[Test]
    public function it_all_roles_returns_collection_of_role_models()
    {
        /** Arrange */
        // Already arranged in setUp()

        /** Act */
        $roles = $this->repository->allRoles();

        /** Assert */
        foreach ($roles as $role) {
            $this->assertInstanceOf(Role::class, $role, 'Each item should be a Role model');
        }
    }

    #[Test]
    public function it_all_roles_includes_administrator_role()
    {
        /** Arrange */
        // Already arranged in setUp()

        /** Act */
        $roles = $this->repository->allRoles();
        $roleNames = $roles->pluck('name')->toArray();

        /** Assert */
        $this->assertContains('administrator', $roleNames, 'allRoles() should include administrator role');
    }

    #[Test]
    public function it_list_all_roles_returns_display_names_keyed_by_id()
    {
        /** Arrange */
        // Already arranged in setUp()

        /** Act */
        $roles = $this->repository->listAllRoles();

        /** Assert */
        $this->assertNotEmpty($roles);

        foreach ($roles as $id => $displayName) {
            $this->assertIsInt($id);
            $this->assertIsString($displayName);
        }
    }

    #[Test]
    public function it_list_all_roles_does_not_include_owner()
    {
        /** Arrange */
        // Already arranged in setUp()

        /** Act */
        $roles = $this->repository->listAllRoles();
        $displayNames = $roles->toArray();
        $ownerRole = Role::where('name', 'owner')->first();

        /** Assert */
        if ($ownerRole) {
            $this->assertArrayNotHasKey($ownerRole->id, $displayNames, 'listAllRoles() should not include the owner role');
        }

        $this->assertNotContains('Owner', $displayNames);
    }

    # endregion

    # region edge_cases

    #[Test]
    public function it_all_roles_is_not_broken_by_column_selection_fix()
    {
        /** Arrange */
        // Already arranged in setUp()

        /** Act */
        $roles = $this->repository->allRoles();
        $filtered = $roles->filter(fn ($r) => $r->name !== 'owner');

        /** Assert */
        $this->assertGreaterThanOrEqual(1, $roles->count(), 'Should return at least 1 non-owner role');
        $this->assertEquals($roles->count(), $filtered->count(), 'allRoles() already filters owner so re-filtering changes nothing');
    }

    # endregion
}
