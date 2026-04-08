<?php

namespace Tests;

use App\Models\Role;
use App\Models\User;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Run migrations before each test
        Artisan::call('migrate:fresh');
        Artisan::call('db:seed');

        // Ensure Faker\Generator is bound for legacy factories
        $this->app->singleton(Generator::class, function () {
            return Factory::create();
        });

        // Ensure "Admin" user exists after migrations

        $this->user = User::firstOrCreate(
            ['name' => 'Admin'],
            [
                'external_id' => (string) Str::uuid(),
                'email' => 'admin@admin.com',
                'password' => bcrypt('admin123'),
            ]
        );
        $this->user->attachRole(Role::where('name', 'owner')->first());
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
            \Carbon\Carbon::parse($expected)->toDateTimeString(),
            \Carbon\Carbon::parse($actual)->toDateTimeString(),
            $message
        );
    }
}
