<?php

namespace Tests\Browser;

use App\Models\User;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;

class LoginTest extends DuskTestCase
{
    /**
     * A Dusk test example.
     *
     * @return void
     */
    public function testExample()
    {
        $user = factory(User::class)->create([
            'password' => bcrypt("secretpassword")
        ]);
        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/login')
                ->type('email', $user->email)
                ->type('password', 'wrongpassword')
                ->press('Login')
                ->assertPathIs('/login')
                ->assertSee('These credentials do not match our records.');
        });
    }

    /**
     * A Dusk test example.
     *
     * @return void
     */
    public function testUserCanLoginSuccessfully()
    {
        $user = factory(User::class)->create([
            'password' => bcrypt("secretpassword")
        ]);
        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/login')
                ->type('email', $user->email)
                ->type('password', 'secretpassword')
                ->press('Login')
                ->assertPathIs('/dashboard');
        });
    }
}
