<?php

namespace Tests\Unit\Roles;

use App\Models\Permission;
use App\Models\Role;
use App\Services\Role\RoleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

#[CoversClass(RoleService::class)]
class RoleServiceTest extends AbstractTestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_creates_role_with_valid_data(): void
    {
        /* Arrange */
        $service = new RoleService();

        /* Act */
        $role = $service->create(['name' => 'manager', 'description' => 'desc']);

        /* Assert */
        $this->assertNotNull($role);
        $this->assertSame('manager', $role->name);
        $this->assertSame('desc', $role->description);
    }

    #[Test]
    public function it_syncs_permissions_filtering_disabled_ones(): void
    {
        /* Arrange */
        $service = new RoleService();
        $role    = Role::factory()->create();
        $p1      = Permission::factory()->create();
        $p2      = Permission::factory()->create();

        /* Act */
        // '1' = enabled, '0' = disabled
        $service->syncPermissions($role, [$p1->id => '1', $p2->id => '0']);

        /* Assert */
        $this->assertSame(1, $role->fresh()->permissions->count());
    }

    #[Test]
    public function it_prevents_deletion_of_admin_role(): void
    {
        /* Arrange */
        $service = new RoleService();
        $blocked = Role::factory()->create(['name' => Role::ADMIN_ROLE]);

        /* Act */
        $result = $service->destroy($blocked);

        /* Assert */
        $this->assertFalse($result);
        $this->assertNotNull(Role::find($blocked->id));
    }

    #[Test]
    public function it_allows_deletion_of_custom_roles(): void
    {
        /* Arrange */
        $service = new RoleService();
        $normal  = Role::factory()->create(['name' => 'custom']);

        /* Act */
        $result = $service->destroy($normal);

        /* Assert */
        $this->assertTrue($result);
    }
}
