<?php

namespace Tests;

use App\Models\Permission;
use App\Models\Role;
use App\Enums\PermissionName;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Cache;
use Artisan;

abstract class AbstractTestCase extends BaseTestCase
{
    use CreatesApplication;

    protected static $schemaIsUpToDate = false; // <-- add this (for this process)

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        if (! static::$schemaIsUpToDate) {
            Artisan::call('migrate:fresh', ['--seed' => true]);
            static::$schemaIsUpToDate = true;
        }

        $this->user = User::factory()->create([
            'email' => fake()->unique()->safeEmail,
            'name' => 'Admin',
        ]);

        // Standardize: Every user starts as an owner to minimize boilerplate 403s
        $this->asOwner();

        $this->actingAs($this->user);
    }

    /**
     * Optimized for Entrust/Laravel 12 Bridge
     */
    public function withPermissions(array|PermissionName $permissions): self
    {
        $permissions = is_array($permissions) ? $permissions : [$permissions];

        // Ensure roles are loaded so the check below works
        $this->user->load('roles');

        foreach ($permissions as $permission) {
            $name = $permission instanceof PermissionName ? $permission->value : $permission;
            $label = $permission instanceof PermissionName ? $permission->label() : $name;

            $p = Permission::firstOrCreate(
                ['name' => $name],
                ['display_name' => $label, 'description' => "$label permission"]
            );

            if ($this->user->roles->isNotEmpty()) {
                $role = $this->user->roles->first();
                // Check database directly or refresh relationship to avoid stale 403s
                if (! $role->hasPermission($name)) {
                    $role->attachPermission($p);
                }
            }
        }

        // Entrust Legacy: Clear the specific cache tags used by the library
        if (config('entrust.cache_enabled', true)) {
            Cache::tags('role_user')->flush();
        }

        // Re-authenticate a fresh instance to clear internal model state
        $this->user = $this->user->fresh(['roles', 'roles.permissions']);
        $this->actingAs($this->user);

        return $this;
    }

    /**
     * Refactored asOwner to use the new Enum for consistency
     */
    public function asOwner()
    {
        $role = Role::firstOrCreate(
            ['name' => 'owner'],
            ['display_name' => 'Owner', 'description' => 'Owner role', 'external_id' => 'owner-role-id']
        );

        // Attach role if not already attached
        if (! $this->user->hasRole('owner')) {
            $this->user->attachRole($role);
        }

        // Bulk grant using the Enum to ensure the "Green" state
        return $this->withPermissions([
            PermissionName::USER_UPDATE,
            PermissionName::USER_DELETE,
            PermissionName::PAYMENT_CREATE,
            PermissionName::PAYMENT_DELETE,
            PermissionName::APPOINTMENT_EDIT,
            PermissionName::APPOINTMENT_DELETE,
            PermissionName::CALENDAR_VIEW,
            PermissionName::CLIENT_CREATE,
            PermissionName::CLIENT_UPDATE,
            PermissionName::CLIENT_DELETE,
            PermissionName::LEAD_CREATE,
            PermissionName::LEAD_DELETE,
            PermissionName::LEAD_UPDATE_STATUS,
            PermissionName::LEAD_UPDATE_DEADLINE,
            PermissionName::LEAD_ASSIGN,
            PermissionName::ABSENCE_MANAGE,
        ]);
    }
}
