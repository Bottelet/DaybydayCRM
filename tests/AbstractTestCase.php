<?php

namespace Tests;

use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
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
        $this->user->attachRole(Role::whereName('owner')->first());

        return $this;
    }

    /**
     * Assign the administrator role to the test user.
     *
     * @return $this
     */
    public function asAdmin()
    {
        $this->user->attachRole(Role::whereName('administrator')->first());

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
