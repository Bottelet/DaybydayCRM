 <?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

class FormValidationErrorTaskTest extends TestCase
{
    use DatabaseTransactions;

    public function setup()
    {
        parent::setup();
        App::setLocale('en');

        $this->createUser();
        $this->createRole();
        $this->createTaskPermission();
        $this->createDepartment();
    }

    /**
     * Test title validation on create task input
     */
    public function testMissingTitle()
    {
        $this->visit('/')
            ->seePageIs('/login')
            ->type('bottelet@flarepoint.com', 'email')
            ->type('admin', 'password')
            ->press('Login')
            ->click('New Task')
            ->seePageIs('/tasks/create')
            ->type($this->faker->text(30, 1), 'description')
            ->press('Create New Task')
            ->see('The title field is required.')
            ->seePageIs('/tasks/create');
    }

    /**
     * Test description validation on create task input
     */
    public function testMissingDescription()
    {
        $this->visit('/')
            ->seePageIs('/login')
            ->type('bottelet@flarepoint.com', 'email')
            ->type('admin', 'password')
            ->press('Login')
            ->click('New Task')
            ->seePageIs('/tasks/create')
            ->type($this->faker->title, 'title')
            ->press('Create New Task')
            ->see('The description field is required.')
            ->seePageIs('/tasks/create');
    }
}