<?php

namespace Tests;

use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Config;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock problematic auth guards for testing
        Config::set('auth.guards.api.driver', 'session');

        $this->user = User::where('name', 'Admin')->first();

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
     * Set the current user as owner role
     *
     * @return $this
     */
    public function asOwner()
    {
        $ownerRole = Role::whereName('owner')->first();
        if ($ownerRole) {
            $this->user->attachRole($ownerRole);
        }
        return $this;
    }

    /**
     * Set the current user as administrator role
     *
     * @return $this
     */
    public function asAdmin()
    {
        $adminRole = Role::whereName('administrator')->first();
        if ($adminRole) {
            $this->user->attachRole($adminRole);
        }
        return $this;
    }

    /**
     * Assert that two dates are equal
     *
     * @param  mixed  $expected
     * @param  mixed  $actual
     * @param  string  $message
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
