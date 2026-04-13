<?php

namespace Tests;

use App\Enums\PermissionName;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

abstract class AbstractTestCase extends BaseTestCase
{
    use CreatesApplication;

    protected static $schemaIsUpToDate = false; // <-- add this (for this process)

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset Faker's unique state to avoid collisions with seeded data
        fake()->unique(true);

        if ( ! static::$schemaIsUpToDate) {
            Artisan::call('migrate:fresh', ['--seed' => true]);
            static::$schemaIsUpToDate = true;
        }

        // Use a guaranteed unique email for the test user
        $uniqueEmail = 'testuser_' . uniqid('', true) . '@example.org';
        $this->user  = User::factory()->create([
            'email' => $uniqueEmail,
            'name'  => 'Admin',
        ]);

        // Standardize: Every user starts as an owner to minimize boilerplate 403s
        $this->asOwner();

        $this->actingAs($this->user);
    }

    /**
     * Optimized for Entrust/Laravel 12 Bridge.
     */
    public function withPermissions(array|PermissionName $permissions): self
    {
        $permissions = is_array($permissions) ? $permissions : [$permissions];

        // 1. Ensure the user has a role to attach permissions to
        $role = $this->user->roles()->first() ?? Role::firstOrCreate(['name' => 'owner']);
        if ( ! $this->user->hasRole($role->name)) {
            $this->user->attachRole($role);
        }

        foreach ($permissions as $permission) {
            $name = $permission instanceof PermissionName ? $permission->value : $permission;

            $p = Permission::firstOrCreate(['name' => $name], ['display_name' => $name]);

            // 2. Attach to the role
            if ( ! $role->hasPermission($name)) {
                $role->attachPermission($p);
            }
        }

        // 3. CRITICAL: Entrust Caching and Auth Guard refresh
        Cache::flush();

        // Refresh the user AND its loaded relationships so Entrust sees the new permissions
        $this->user = $this->user->fresh(['roles', 'roles.permissions']);

        // Re-bind to the Auth guard so the FormRequest's auth()->user() is updated
        $this->actingAs($this->user);

        return $this;
    }

    /**
     * Refactored asOwner to use the new Enum for consistency.
     */
    public function asOwner()
    {
        $role = Role::firstOrCreate(
            ['name' => 'owner'],
            ['display_name' => 'Owner', 'description' => 'Owner role', 'external_id' => 'owner-role-id']
        );

        // Attach role if not already attached
        if ( ! $this->user->hasRole('owner')) {
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
            PermissionName::ABSENCE_VIEW,
            PermissionName::PROJECT_DELETE,
            PermissionName::PROJECT_UPDATE,
            PermissionName::PROJECT_UPDATE_STATUS,
            PermissionName::PROJECT_ASSIGN,
            PermissionName::TASK_CREATE,
            PermissionName::TASK_DELETE,
            PermissionName::TASK_UPDATE_STATUS,
            PermissionName::TASK_ASSIGN,
            PermissionName::DOCUMENT_VIEW,
            PermissionName::DOCUMENT_DELETE,
            PermissionName::MODIFY_INVOICE_LINES,
        ]);
    }

    /**
     * Assigns administrator role and permissions to the test user for admin-level tests.
     */
    public function asAdmin()
    {
        $role = \App\Models\Role::firstOrCreate(
            ['name' => 'admin'],
            ['display_name' => 'Administrator', 'description' => 'Administrator role', 'external_id' => 'admin-role-id']
        );

        // Attach role if not already attached
        if ( ! $this->user->hasRole('admin')) {
            $this->user->attachRole($role);
        }

        // Grant a broad set of permissions for admin (can be adjusted as needed)
        return $this->withPermissions([
            \App\Enums\PermissionName::USER_UPDATE,
            \App\Enums\PermissionName::USER_DELETE,
            \App\Enums\PermissionName::CLIENT_CREATE,
            \App\Enums\PermissionName::CLIENT_UPDATE,
            \App\Enums\PermissionName::CLIENT_DELETE,
            \App\Enums\PermissionName::LEAD_CREATE,
            \App\Enums\PermissionName::LEAD_DELETE,
            \App\Enums\PermissionName::LEAD_UPDATE_STATUS,
            \App\Enums\PermissionName::LEAD_UPDATE_DEADLINE,
            \App\Enums\PermissionName::LEAD_ASSIGN,
            \App\Enums\PermissionName::PAYMENT_CREATE,
            \App\Enums\PermissionName::PAYMENT_DELETE,
            \App\Enums\PermissionName::APPOINTMENT_EDIT,
            \App\Enums\PermissionName::APPOINTMENT_DELETE,
            \App\Enums\PermissionName::CALENDAR_VIEW,
            \App\Enums\PermissionName::ABSENCE_MANAGE,
            \App\Enums\PermissionName::PROJECT_DELETE,
            \App\Enums\PermissionName::PROJECT_UPDATE,
            \App\Enums\PermissionName::PROJECT_UPDATE_STATUS,
            \App\Enums\PermissionName::PROJECT_ASSIGN,
            \App\Enums\PermissionName::TASK_CREATE,
            \App\Enums\PermissionName::TASK_DELETE,
            \App\Enums\PermissionName::TASK_UPDATE_STATUS,
            \App\Enums\PermissionName::TASK_ASSIGN,
            \App\Enums\PermissionName::DOCUMENT_VIEW,
            \App\Enums\PermissionName::DOCUMENT_DELETE,
            \App\Enums\PermissionName::MODIFY_INVOICE_LINES,
        ]);
    }

    protected function followRedirectsAndFail($response)
    {
        if ($response->isRedirect()) {
            // If we got a redirect, it means canAccessDocument returned false.
            // Let's find out WHY by looking at the session.
            $message = session('flash_message_warning') ?? 'Redirected without message';
            $this->fail('Test failed with a 302 Redirect. Session Message: ' . $message);
        }
    }
}
