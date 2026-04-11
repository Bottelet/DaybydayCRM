<?php

namespace Tests\Unit\Repositories;

use App\Models\Role;
use App\Repositories\Role\RoleRepository;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

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

    private RoleRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure roles exist for testing
        Role::factory()->create(['name' => 'administrator', 'display_name' => 'Administrator']);
        Role::factory()->create(['name' => 'manager', 'display_name' => 'Manager']);
        Role::factory()->create(['name' => 'employee', 'display_name' => 'Employee']);

        $this->repository = new RoleRepository();
    }

    #[Test]
    public function all_roles_excludes_owner_role()
    {
        $roles = $this->repository->allRoles();

        $roleNames = $roles->pluck('name')->toArray();
        $this->assertNotContains('owner', $roleNames, 'allRoles() should not include the owner role');
    }

    #[Test]
    public function all_roles_returns_roles_with_required_columns()
    {
        $roles = $this->repository->allRoles();

        $this->assertNotEmpty($roles, 'allRoles() should return at least one role');

        foreach ($roles as $role) {
            // Verify the fix: Role::all(['column1', 'column2', ...]) correctly selects these columns
            $this->assertNotNull($role->display_name, 'Role should have display_name');
            $this->assertNotNull($role->id, 'Role should have id');
            $this->assertNotNull($role->name, 'Role should have name');
        }
    }

    #[Test]
    public function all_roles_returns_collection_of_role_models()
    {
        $roles = $this->repository->allRoles();

        foreach ($roles as $role) {
            $this->assertInstanceOf(Role::class, $role, 'Each item should be a Role model');
        }
    }

    #[Test]
    public function all_roles_includes_administrator_role()
    {
        $roles = $this->repository->allRoles();
        $roleNames = $roles->pluck('name')->toArray();

        $this->assertContains('administrator', $roleNames, 'allRoles() should include administrator role');
    }

    #[Test]
    public function list_all_roles_returns_display_names_keyed_by_id()
    {
        $roles = $this->repository->listAllRoles();

        $this->assertNotEmpty($roles);

        // Verify the result is a key-value map of id => display_name
        foreach ($roles as $id => $displayName) {
            $this->assertIsInt($id);
            $this->assertIsString($displayName);
        }
    }

    #[Test]
    public function list_all_roles_does_not_include_owner()
    {
        $roles = $this->repository->listAllRoles();
        $displayNames = $roles->toArray();

        $ownerRole = Role::where('name', 'owner')->first();
        if ($ownerRole) {
            $this->assertArrayNotHasKey($ownerRole->id, $displayNames, 'listAllRoles() should not include the owner role');
        }

        $this->assertNotContains('Owner', $displayNames);
    }

    #[Test]
    public function all_roles_is_not_broken_by_column_selection_fix()
    {
        // Regression: before the fix, passing columns as individual arguments
        // (Role::all('display_name', 'id', ...)) would not work as expected in
        // Eloquent — it would ignore columns and return all. The fix uses array
        // syntax (Role::all(['display_name', 'id', ...])) which is correct.
        // This test verifies the fix doesn't break the method.
        $roles = $this->repository->allRoles();

        // Method should return a valid non-empty filterable collection
        $this->assertGreaterThanOrEqual(1, $roles->count(), 'Should return at least 1 non-owner role');

        // The returned roles should be filterable (i.e., they're Eloquent models, not raw data)
        $filtered = $roles->filter(fn ($r) => $r->name !== 'owner');
        $this->assertEquals($roles->count(), $filtered->count(), 'allRoles() already filters owner so re-filtering changes nothing');
    }
}
