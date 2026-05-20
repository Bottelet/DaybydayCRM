<?php

namespace Tests\Feature\Commands;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

#[Group('upgrade-command')]
class UpgradeCommandTest extends AbstractTestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_command_executes_successfully()
    {
        /* Arrange */
        Role::factory()->create(['name' => 'owner', 'display_name' => 'Owner']);
        Role::factory()->create(['name' => 'administrator', 'display_name' => 'Administrator']);

        /* Act */
        $this->artisan('daybyday:upgrade')->assertExitCode(0);
    }

    #[Test]
    public function it_command_creates_missing_permissions()
    {
        /* Arrange */
        Role::factory()->create(['name' => 'owner', 'display_name' => 'Owner']);
        Role::factory()->create(['name' => 'administrator', 'display_name' => 'Administrator']);

        /* Act */
        $this->artisan('daybyday:upgrade');

        /* Assert */
        $this->assertTrue(Permission::where('name', 'user-view')->exists());
        $this->assertTrue(Permission::where('name', 'client-view')->exists());
        $this->assertTrue(Permission::where('name', 'lead-view')->exists());
        $this->assertTrue(Permission::where('name', 'project-update')->exists());
    }

    #[Test]
    public function it_command_assigns_all_permissions_to_owner_role()
    {
        /* Arrange */
        $ownerRole = Role::factory()->create(['name' => 'owner', 'display_name' => 'Owner']);
        Role::factory()->create(['name' => 'administrator', 'display_name' => 'Administrator']);

        /* Act */
        $this->artisan('daybyday:upgrade');

        /* Assert */
        $ownerPermCount = $ownerRole->perms()->count();
        $totalPermCount = Permission::count();
        $this->assertEquals($totalPermCount, $ownerPermCount);
        $this->assertGreaterThanOrEqual(61, $ownerPermCount);
    }

    #[Test]
    public function it_command_assigns_all_permissions_to_admin_role()
    {
        /* Arrange */
        Role::factory()->create(['name' => 'owner', 'display_name' => 'Owner']);
        $adminRole = Role::factory()->create(['name' => 'administrator', 'display_name' => 'Administrator']);

        /* Act */
        $this->artisan('daybyday:upgrade');

        /* Assert */
        $adminPermCount = $adminRole->perms()->count();
        $totalPermCount = Permission::count();
        $this->assertEquals($totalPermCount, $adminPermCount);
        $this->assertGreaterThanOrEqual(61, $adminPermCount);
    }

    #[Test]
    public function it_command_is_idempotent_safe_to_run_multiple_times()
    {
        /* Arrange */
        Role::factory()->create(['name' => 'owner', 'display_name' => 'Owner']);
        Role::factory()->create(['name' => 'administrator', 'display_name' => 'Administrator']);

        /* Act */
        $this->artisan('daybyday:upgrade');
        $firstRun = Permission::count();

        $this->artisan('daybyday:upgrade');
        $secondRun = Permission::count();

        /* Assert */
        $this->assertEquals($firstRun, $secondRun);
    }

    #[Test]
    public function it_command_does_not_delete_existing_permissions()
    {
        /* Arrange */
        Role::factory()->create(['name' => 'owner', 'display_name' => 'Owner']);
        Role::factory()->create(['name' => 'administrator', 'display_name' => 'Administrator']);

        $existingPerm   = Permission::factory()->create(['name' => 'existing-perm']);
        $existingPermId = $existingPerm->id;

        /* Act */
        $this->artisan('daybyday:upgrade');

        /* Assert */
        $this->assertTrue(Permission::where('id', $existingPermId)->exists());
    }

    #[Test]
    public function it_command_does_not_delete_existing_role_assignments()
    {
        /* Arrange */
        $owner = Role::factory()->create(['name' => 'owner', 'display_name' => 'Owner']);
        Role::factory()->create(['name' => 'administrator', 'display_name' => 'Administrator']);

        $customPerm = Permission::factory()->create(['name' => 'custom-permission']);
        $owner->perms()->attach($customPerm->id);

        /* Act */
        $this->artisan('daybyday:upgrade');

        /* Assert */
        $this->assertTrue($owner->perms()->where('id', $customPerm->id)->exists());
    }

    #[Test]
    public function it_command_handles_missing_roles_gracefully()
    {
        /* Arrange */

        /* Act */
        $this->artisan('daybyday:upgrade')->assertExitCode(0);
    }

    #[Test]
    public function it_command_preserves_existing_user_data()
    {
        /* Arrange */
        Role::factory()->create(['name' => 'owner', 'display_name' => 'Owner']);
        Role::factory()->create(['name' => 'administrator', 'display_name' => 'Administrator']);

        $user          = User::factory()->create(['name' => 'Test User', 'email' => 'test@test.com']);
        $originalEmail = $user->email;

        /* Act */
        $this->artisan('daybyday:upgrade');

        /* Assert */
        $user->refresh();
        $this->assertEquals($originalEmail, $user->email);
    }

    #[Test]
    public function it_command_syncs_only_to_owner_and_admin_roles()
    {
        /* Arrange */
        $ownerRole   = Role::factory()->create(['name' => 'owner', 'display_name' => 'Owner']);
        $adminRole   = Role::factory()->create(['name' => 'administrator', 'display_name' => 'Administrator']);
        $managerRole = Role::factory()->create(['name' => 'manager', 'display_name' => 'Manager']);

        /* Act */
        $this->artisan('daybyday:upgrade');

        /* Assert */
        $ownerPerms   = $ownerRole->perms()->count();
        $adminPerms   = $adminRole->perms()->count();
        $managerPerms = $managerRole->perms()->count();
        $totalPerms   = Permission::count();

        $this->assertEquals($totalPerms, $ownerPerms);
        $this->assertEquals($totalPerms, $adminPerms);
        $this->assertEquals(0, $managerPerms);
    }

    #[Test]
    public function it_command_syncs_permissions_to_admin_role_alias()
    {
        /* Arrange */
        Role::factory()->create(['name' => 'owner', 'display_name' => 'Owner']);
        $adminRole = Role::factory()->create(['name' => 'admin', 'display_name' => 'Administrator']);

        /* Act */
        $this->artisan('daybyday:upgrade');

        /* Assert */
        $adminPermCount = $adminRole->perms()->count();
        $totalPermCount = Permission::count();
        $this->assertEquals($totalPermCount, $adminPermCount);
        $this->assertGreaterThanOrEqual(61, $adminPermCount);
    }

    #[Test]
    public function it_all_critical_permissions_are_created()
    {
        /* Arrange */
        Role::factory()->create(['name' => 'owner', 'display_name' => 'Owner']);
        Role::factory()->create(['name' => 'administrator', 'display_name' => 'Administrator']);

        $criticalPerms = [
            'user-view', 'client-view', 'client-create', 'client-update', 'client-delete',
            'lead-view', 'lead-create', 'lead-update-status', 'lead-delete',
            'task-create', 'task-delete', 'task-update-status', 'project-create', 'project-update',
            'payment-create', 'payment-update', 'payment-delete', 'document-view', 'document-upload',
            'invoice-see', 'invoice-send', 'offer-create', 'product-create', 'absence-manage',
        ];

        /* Act */
        $this->artisan('daybyday:upgrade');

        /* Assert */
        foreach ($criticalPerms as $perm) {
            $this->assertTrue(
                Permission::where('name', $perm)->exists(),
                "Permission '{$perm}' should exist"
            );
        }
    }

    #[Test]
    public function it_command_runs_without_affecting_other_data()
    {
        /* Arrange */
        Role::factory()->create(['name' => 'owner', 'display_name' => 'Owner']);
        Role::factory()->create(['name' => 'administrator', 'display_name' => 'Administrator']);

        $userCount = User::count();
        $roleCount = Role::count();

        /* Act */
        $this->artisan('daybyday:upgrade');

        /* Assert */
        $this->assertEquals($userCount, User::count());
        $this->assertEquals($roleCount, Role::count());
    }
}
