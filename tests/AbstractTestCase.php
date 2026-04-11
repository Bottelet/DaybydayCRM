<?php

namespace Tests;

use App\Models\Setting;
use App\Models\BusinessHour;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
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
            // The app container & facades are initialized HERE
            Artisan::call('migrate:fresh', ['--seed' => true]);
            static::$schemaIsUpToDate = true;
        }

        // Every test: build fresh, unique data only!
        $this->user = User::factory()->create([
            // Unique email for this test!
            'email' => fake()->unique()->safeEmail,
            'name' => 'Admin',
        ]);

        // Attach role using factories/helpers, not first()
        $ownerRole = Role::query()->where('name', 'owner')->first() ?? Role::factory()->create(['name' => 'owner']);
        $this->user->roles()->attach($ownerRole->id);

        $setting = Setting::first() ?? Setting::factory()->create();
        if (BusinessHour::count() == 0) {
            foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
                BusinessHour::factory()->create([
                    'day' => $day,
                    'settings_id' => $setting->id,
                ]);
            }
        }

        $this->actingAs($this->user);
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param  mixed  $user
     */
    public function setUser($user): void
    {
        $this->user = $user;
        $this->actingAs($user);
    }

    /**
     * Assign the owner role to the test user.
     *
     * @return $this
     */
    public function asOwner()
    {
        $role = Role::firstOrCreate(['name' => 'owner'], ['display_name' => 'Owner', 'description' => 'Owner role', 'external_id' => 'owner-role-id']);

        // Comprehensive permission set for owner role
        $permissions = [
            'user-update' => ['display_name' => 'Update User', 'description' => 'Update user permission'],
            'user-delete' => ['display_name' => 'Delete User', 'description' => 'Delete user permission'],
            'payment-create' => ['display_name' => 'Create Payment', 'description' => 'Create payment permission'],
            'payment-delete' => ['display_name' => 'Delete Payment', 'description' => 'Delete payment permission'],
            'appointment-edit' => ['display_name' => 'Edit Appointment', 'description' => 'Edit appointment permission'],
            'appointment-delete' => ['display_name' => 'Delete Appointment', 'description' => 'Delete appointment permission'],
            'calendar-view' => ['display_name' => 'View Calendar', 'description' => 'View calendar permission'],
            'client-create' => ['display_name' => 'Create Client', 'description' => 'Create client permission'],
            'client-update' => ['display_name' => 'Update Client', 'description' => 'Update client permission'],
            'client-delete' => ['display_name' => 'Delete Client', 'description' => 'Delete client permission'],
            'lead-delete' => ['display_name' => 'Delete Lead', 'description' => 'Delete lead permission'],
            'absence-manage' => ['display_name' => 'Manage Absence', 'description' => 'Manage absence permission'],
        ];

        foreach ($permissions as $name => $details) {
            $permission = Permission::firstOrCreate(['name' => $name], $details);
            if (! $role->hasPermission($name)) {
                $role->attachPermission($permission);
            }
        }

        $this->user->attachRole($role);
        Cache::tags('role_user')->flush();

        return $this;
    }

    /**
     * Assign the administrator role to the test user.
     *
     * @return $this
     */
    public function asAdmin()
    {
        $role = Role::firstOrCreate(['name' => 'administrator'], ['display_name' => 'Administrator', 'description' => 'Administrator role', 'external_id' => 'admin-role-id']);

        // Also ensure user-update permission exists and is attached to admin role
        $permission = Permission::firstOrCreate(['name' => 'user-update'], ['display_name' => 'Update User', 'description' => 'Update user permission']);
        if (! $role->hasPermission('user-update')) {
            $role->attachPermission($permission);
        }

        $this->user->attachRole($role);
        Cache::tags('role_user')->flush();

        return $this;
    }

    /**
     * Grant specific permissions to the current test user.
     *
     * @param  array|string  $permissions
     * @return $this
     */
    /*
    public function withPermissions($permissions)
    {
        $permissions = is_array($permissions) ? $permissions : [$permissions];

        foreach ($permissions as $permissionName) {
            $permission = Permission::firstOrCreate(
                ['name' => $permissionName],
                [
                    'display_name' => ucfirst(str_replace('-', ' ', $permissionName)),
                    'description' => ucfirst(str_replace('-', ' ', $permissionName)).' permission',
                ]
            );

            // Attach permission to user's first role
            if ($this->user->roles->isNotEmpty()) {
                $role = $this->user->roles->first();
                if (! $role->hasPermission($permissionName)) {
                    $role->attachPermission($permission);
                }
            }
        }

        Cache::tags('role_user')->flush();

        return $this;
    }
    */

    /**
     * Grant specific permissions to the current test user.
     *
     * @param  array|string  $permissions
     * @return $this
     */
public function withPermissions(array|PermissionName $permissions)
{
    $permissions = is_array($permissions) ? $permissions : [$permissions];

    foreach ($permissions as $permission) {
        // Use ->value to satisfy Entrust's string requirement
        $name = $permission instanceof PermissionName ? $permission->value : $permission;
        
        Permission::firstOrCreate(['name' => $name]);
        // ... rest of your attachment logic
        /*
            $permission = Permission::firstOrCreate(
                ['name' => $permissionName],
                [
                    'display_name' => ucfirst(str_replace('-', ' ', $permissionName)),
                    'description' => ucfirst(str_replace('-', ' ', $permissionName)).' permission',
                ]
            );

            // Attach permission to user's first role
            if ($this->user->roles->isNotEmpty()) {
                $role = $this->user->roles->first();
                if (! $role->hasPermission($permissionName)) {
                    $role->attachPermission($permission);
                }
            }
        */
    }
    
    Cache::tags('role_user')->flush();
    $this->actingAs($this->user->fresh()); 
    return $this;
}
    
    /**
     * Custom assertion to compare dates accurately regardless of format.
     *
     * @param  mixed  $expected
     * @param  mixed  $actual
     * @param  string  $message
     * @return void
     */
    public function assertDatesEqual($expected, $actual, $message = '')
    {
        $this->assertEquals(
            Carbon::parse($expected)->toDateTimeString(),
            Carbon::parse($actual)->toDateTimeString(),
            $message
        );
    }
}
