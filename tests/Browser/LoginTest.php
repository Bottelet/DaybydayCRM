<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Test;
use Tests\DuskTestCase;

class LoginTest extends DuskTestCase
{
    #[Test]
    public function it_example()
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
                ->press('Login');
        });

        /* Assert */
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->assertPathIs('/login')
                ->assertSee('These credentials do not match our records.');
        });
    }

    #[Test]
    public function it_user_can_login_successfully()
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
                ->press('Login');
        });

        /* Assert */
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->assertPathIs('/dashboard');
        });
    }
}
