 <?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

class FormValidationErrorUserTest extends TestCase
{
    use DatabaseTransactions;

    public function setup()
    {
        parent::setup();

        $this->createUser();
        $this->createRole();
        $this->createUserPermission();
    }

    /**
     * Test name validation on create user form
     */
    public function testMissingName()
    {
        $this->visit('/')
            ->seePageIs('/login')
            ->type('bottelet@flarepoint.com', 'email')
            ->type('admin', 'password')
            ->press('Login')
            ->click('New User')
            ->seePageIs('/users/create')
            ->type($this->faker->email, 'email')
            ->type('TestPassword', 'password')
            ->type('TestPassword', 'password_confirmation')
            ->press('Create new user')
            ->see('The name field is required')
            ->seePageIs('/users/create');
    }

    /**
     * Test email validation on create user form
     */
    public function testMissingEmail()
    {
        $this->visit('/')
            ->seePageIs('/login')
            ->type('bottelet@flarepoint.com', 'email')
            ->type('admin', 'password')
            ->press('Login')
            ->click('New User')
            ->seePageIs('/users/create')
            ->type($this->faker->name, 'name')
            ->type('TestPassword', 'password')
            ->type('TestPassword', 'password_confirmation')
            ->press('Create new user')
            ->see('The email field is required.')
            ->seePageIs('/users/create');
    }

    /**
     * Test password validation on create user form
     */
    public function testMissingPassword()
    {
        $this->visit('/')
            ->seePageIs('/login')
            ->type('bottelet@flarepoint.com', 'email')
            ->type('admin', 'password')
            ->press('Login')
            ->click('New User')
            ->seePageIs('/users/create')
            ->type($this->faker->name, 'name')
            ->type($this->faker->email, 'email')
            ->press('Create new user')
            ->see('The password field is required.')
            ->see('The password confirmation field is required.')
            ->seePageIs('/users/create');
    }

    /**
     * Test password does not match validation on create user form
     */
    public function testNotMatchingPasswords()
    {
        $this->visit('/')
            ->seePageIs('/login')
            ->type('bottelet@flarepoint.com', 'email')
            ->type('admin', 'password')
            ->press('Login')
            ->click('New User')
            ->seePageIs('/users/create')
            ->type($this->faker->name, 'name')
            ->type($this->faker->email, 'email')
            ->type('TestPassword', 'password')
            ->type('TestPasswordWrong', 'password_confirmation')
            ->press('Create new user')
            ->see('The password confirmation does not match.')
            ->seePageIs('/users/create');
    }
}