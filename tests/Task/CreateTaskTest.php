<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

class CreateTaskTest extends TestCase
{
    use DatabaseTransactions;

    public function setup()
    {
        parent::setup();
        App::setLocale('en');

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
            ->dontSee('New Task')
            ->visit('/tasks/create')
            ->see('Not allowed to create task')
            ->seePageIs('/tasks');
    }

    /**
     * Test user can create task with correct permission
     */
    public function testCanCreateClientWithPermission()
    {
        $this->createClient();
        $this->createTaskPermission();

        $this->visit('/')
            ->seePageIs('/login')
            ->type('bottelet@flarepoint.com', 'email')
            ->type('admin', 'password')
            ->press('Login')
            ->see('Tasks')
            ->click('New Task')
            ->seePageIs('/tasks/create')
            ->type($this->faker->title, 'title')
            ->type($this->faker->realText(30, 3), 'description')
            ->type($this->faker->date(), 'deadline')
            ->select(1, 'status')
            ->press('Create New Task')
            ->see('Task successfully added');
    }

}