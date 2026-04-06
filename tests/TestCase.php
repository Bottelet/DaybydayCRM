<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        \Artisan::call('migrate:fresh');
        // Ensure the Admin user exists for every test
        $this->user = User::firstOrCreate(
            ['name' => 'Admin'],
            [
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
