 <?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

class FormValidationErrorLeadTest extends TestCase
{
    use DatabaseTransactions;

    public function setup()
    {
        parent::setup();
        App::setLocale('en');

        $this->createUser();
        $this->createRole();
        $this->createLeadPermission();
    }

    /**
     * Test title validation on create lead input
     */
    public function testMissingTitle()
    {
        $this->visit('/')
            ->seePageIs('/login')
            ->type('bottelet@flarepoint.com', 'email')
            ->type('admin', 'password')
            ->press('Login')
            ->click('New Lead')
            ->seePageIs('/leads/create')
            ->type($this->faker->text(30, 1), 'note')
            ->press('Create New Lead')
            ->see('The title field is required.')
            ->seePageIs('/leads/create');
    }

    /**
     * Test note validation on create lead input
     */
    public function testMissingDescription()
    {
        $this->visit('/')
            ->seePageIs('/login')
            ->type('bottelet@flarepoint.com', 'email')
            ->type('admin', 'password')
            ->press('Login')
            ->click('New Lead')
            ->seePageIs('/leads/create')
            ->type($this->faker->title, 'title')
            ->press('Create New Lead')
            ->see('The note field is required.')
            ->seePageIs('/leads/create');
    }
}