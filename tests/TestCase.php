<?php

namespace Tests;

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
}
