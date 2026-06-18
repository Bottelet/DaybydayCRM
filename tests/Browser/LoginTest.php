<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Test;
use Tests\DuskTestCase;

class LoginTest extends DuskTestCase
{
    /**
     * A Dusk test example.
     *
     * @return void
     */
    #[Test]
    public function it_loads_the_login_page()
    {
        /* Arrange */
        $user = User::factory()->create([
            'password' => bcrypt('secretpassword'),
        ]);

        /* Act */
        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/login')
                ->type('email', $user->email)
                ->type('password', 'wrongpassword')
                ->press('Login')
                ->assertPathIs('/login')
                ->assertSee('These credentials do not match our records.');
        });

        /* Assert */
    }

    /**
     * A Dusk test example.
     *
     * @return void
     */
    #[Test]
    public function it_logs_in_successfully()
    {
        /* Arrange */
        $user = User::factory()->create([
            'password' => bcrypt('secretpassword'),
        ]);

        /* Act */
        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/login')
                ->type('email', $user->email)
                ->type('password', 'secretpassword')
                ->press('Login')
                ->assertPathIs('/dashboard');
        });

        /* Assert */
    }
}
