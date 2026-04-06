<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected $user;

    protected function setUp(): void
    {
        // Run migrations before each test
        Artisan::call('migrate:fresh');

        // Ensure "Admin" user exists
        $this->user = User::firstOrCreate(
            ['name' => 'Admin'],
            [
                'email' => 'admin@admin.com',
                'password' => bcrypt('admin123'),
            ]
        );

        parent::setUp();

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
