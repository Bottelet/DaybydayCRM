<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

class CreateUserTest extends TestCase
{
    use DatabaseTransactions;

    public function setup()
    {
        parent::setup();

        $this->createUser();
        $this->createRole();

    }

    /**
     * Test yser can not create a task without permission
     */
    public function testCanNotAccessCreatePageWithOutPermission()
    {
        $this->visit('/')
            ->seePageIs('/login')
            ->type('bottelet@flarepoint.com', 'email')
            ->type('admin', 'password')
            ->press('Login')
            ->see('Tasks')
            ->dontSee('New User')
            ->visit('/users/create')
            ->see('Not allowed to create user')
            ->seePageIs('/users');
    }

    /**
     * Test user can create task with correct permission
     */
    public function testCanCreateClientWithPermission()
    {
        $this->createUserPermission();

        $this->visit('/')
            ->seePageIs('/login')
            ->type('bottelet@flarepoint.com', 'email')
            ->type('admin', 'password')
            ->press('Login')
            ->see('Tasks')
            ->click('New User')
            ->seePageIs('/users/create')
            ->type($this->faker->name, 'name')
            ->type($this->faker->email, 'email')
            ->type($this->faker->address, 'address')
            ->type($this->faker->randomNumber(8), 'personal_number')
            ->type($this->faker->randomNumber(8), 'work_number')
            ->type('password', 'password')
            ->type('password', 'password_confirmation')
            ->press('Create new user')
            ->see('User successfully added');
    }

}