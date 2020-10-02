<?php

namespace Tests\Browser;

use App\Models\Setting;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use App\Models\User;
use Faker\Factory as Faker;

class UserTest extends DuskTestCase
{
    /**
     * Test user can access user thorugh index page.
     */
    public function testUserCanSeeUsersOnUserIndexAndGoToTheUserWithLink()
    {
        $user = factory(User::class)->create();
        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->visit('/users')
                ->type('.dataTables_filter input', $user->name)
                ->waitForText($user->name)
                ->clickLink($user->name)
                ->assertPathIs('/users/' . $user->external_id)
                ->waitForText($user->name);
        });
    }

    /**
     * Test user can see all the correct info on user page
     */
    public function testICanSeeAllTheCorrectInformationOnUserInfoPage()
    {
        $user = factory(User::class)->create();
        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->visit('/users/' . $user->external_id)
                ->waitForText($user->name)
                ->assertSee($user->primary_number)
                ->assertSee($user->secondary_number);
        });
    }

    /**
     * Test i can create a new User
     */
    public function testICanCreateANewUser()
    {
        Setting::whereId(1)->update(['max_users' => 10000000]);
        $faker = Faker::create();
        $this->browse(function (Browser $browser) use ($faker) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->visit('/users/create')
                ->waitForText("Create user")
                ->type('name', $faker->name)
                ->type('email', $faker->email)
                ->type('primary_number', $faker->randomNumber(8))
                ->type('secondary_number', $faker->randomNumber(8))
                ->type('address', $faker->secondaryAddress)
                ->type('password', "Password123")
                ->type('password_confirmation', "Password123")
                ->select("roles", 1)
                ->select("departments", 1)
                ->press('Create user')
                ->assertSee('User successfully added');
        });
    }
}
