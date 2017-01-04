<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

class CreateLeadTest extends TestCase
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
     * Test yser can not create a lead without permission
     */
    public function testCanNotAccessCreatePageWithOutPermission()
    {
        $this->visit('/')
            ->seePageIs('/login')
            ->type('bottelet@flarepoint.com', 'email')
            ->type('admin', 'password')
            ->press('Login')
            ->see('leads')
            ->dontSee('New Lead')
            ->visit('/leads/create')
            ->see('Not allowed to create lead')
            ->seePageIs('/leads');
    }

    /**
     * Test user can create lead with correct permission
     */
    public function testCanCreateClientWithPermission()
    {
        $this->createClient();
        $this->createLeadPermission();

        $this->visit('/')
            ->seePageIs('/login')
            ->type('bottelet@flarepoint.com', 'email')
            ->type('admin', 'password')
            ->press('Login')
            ->see('leads')
            ->click('New Lead')
            ->seePageIs('/leads/create')
            ->type($this->faker->title, 'title')
            ->type($this->faker->realText(30, 3), 'note')
            ->type($this->faker->date(), 'contact_date')
            ->select(1, 'status')
            ->press('Create New Lead')
            ->see('Lead is created');
    }

}