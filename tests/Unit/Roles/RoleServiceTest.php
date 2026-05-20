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
        $service = new RoleService();

        $role = $service->create(['name' => 'manager', 'description' => 'desc']);

        $this->assertNotNull($role);
        $this->assertSame('manager', $role->name);
        $this->assertSame('desc', $role->description);
    }

    #[Test]
    public function it_syncs_permissions_filtering_disabled_ones(): void
    {
        $service = new RoleService();
        $role    = Role::factory()->create();
        $p1      = Permission::factory()->create();
        $p2      = Permission::factory()->create();

        // '1' = enabled, '0' = disabled
        $service->syncPermissions($role, [$p1->id => '1', $p2->id => '0']);

        $this->assertSame(1, $role->fresh()->permissions->count());
    }

    #[Test]
    public function it_prevents_deletion_of_admin_role(): void
    {
        $service = new RoleService();
        $blocked = Role::factory()->create(['name' => Role::ADMIN_ROLE]);

        $this->assertFalse($service->destroy($blocked));
    }

    #[Test]
    public function it_allows_deletion_of_custom_roles(): void
    {
        $service = new RoleService();
        $normal  = Role::factory()->create(['name' => 'custom']);

        $this->assertTrue($service->destroy($normal));
    }
}
