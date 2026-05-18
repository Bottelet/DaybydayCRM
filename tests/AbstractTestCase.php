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
        $role = $this->user->roles()->first() ?? Role::query()->firstOrCreate(['name' => 'owner']);
        if ( ! $this->user->hasRole($role->name)) {
            $this->user->attachRole($role);
        }

        foreach ($permissions as $permission) {
            $name = $permission instanceof PermissionName ? $permission->value : $permission;

            $p = Permission::query()->firstOrCreate(['name' => $name], ['display_name' => $name]);

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
     * Refactored asOwner to grant ALL permissions from the enum — never needs manual updates.
     */
    public function asOwner(): self
    {
        $role = Role::query()->firstOrCreate(
            ['name' => 'owner'],
            ['display_name' => 'Owner', 'description' => 'Owner role', 'external_id' => 'owner-role-id']
        );

        if ( ! $this->user->hasRole('owner')) {
            $this->user->attachRole($role);
        }

        return $this->withPermissions(PermissionName::cases());
    }

    /**
     * Assigns administrator role with ALL permissions to the test user.
     */
    public function asAdmin(): self
    {
        $role = Role::query()->firstOrCreate(
            ['name' => 'administrator'],
            ['display_name' => 'Administrator', 'description' => 'Administrator role', 'external_id' => 'admin-role-id']
        );

        if ( ! $this->user->hasRole('administrator')) {
            $this->user->attachRole($role);
        }

        return $this->withPermissions(PermissionName::cases());
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

    /**
     * Set the application URL for the duration of a single test.
     * Updates both the config repository and the URL generator so that
     * url() / route() calls respect the new value immediately.
     */
    protected function setAppUrl(string $url): void
    {
        config(['app.url' => $url]);
        app('url')->forceRootUrl($url);
        if ($scheme = parse_url($url, PHP_URL_SCHEME)) {
            app('url')->forceScheme($scheme);
        }
    }
}
