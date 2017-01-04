 <?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

class UserPageTest extends TestCase
{
    use DatabaseTransactions;

    public function setup()
    {
        parent::setup();
        App::setLocale('en');

        $this->createUser();
        $this->createClient();
        $this->createRole();
        $this->createDepartment();

	}

    /**
     * Test that we can see the correct table for tasks
     */
    public function testTaskTab()
    {
        $this->visit('/')
            ->seePageIs('/login')
            ->type('bottelet@flarepoint.com', 'email')
            ->type('admin', 'password')
            ->press('Login')
            ->visit('users/' . $this->user->id)
            ->seeInElement('el-tab-pane', 'Assigned Tasks')
            ->seeInElement('thead', 'Title')
            ->seeInElement('thead', 'Client')
            ->seeInElement('thead', 'Created')
            ->seeInElement('thead', 'Deadline')
            ->seeInElement('thead', 'Status');   
    }

    /**
     * Test that we can see the correct table for leads
     */
    public function testLeadTab()
    {
        $this->visit('/')
            ->seePageIs('/login')
            ->type('bottelet@flarepoint.com', 'email')
            ->type('admin', 'password')
            ->press('Login')
            ->visit('users/' . $this->user->id)
            ->seeInElement('el-tab-pane', 'Assigned Leads')
            ->seeInElement('thead', 'Title')
            ->seeInElement('thead', 'Client')
            ->seeInElement('thead', 'Created')
            ->seeInElement('thead', 'Next follow up')
            ->seeInElement('thead', 'Status');   
    }

    /**
     * Test that we can see the correct table for clients
     */
    public function testClientTab()
    {
        $this->visit('/')
            ->seePageIs('/login')
            ->type('bottelet@flarepoint.com', 'email')
            ->type('admin', 'password')
            ->press('Login')
            ->visit('users/' . $this->user->id)
            ->seeInElement('el-tab-pane', 'Assigned Clients')
            ->seeInElement('thead', 'Name')
            ->seeInElement('thead', 'Company')
            ->seeInElement('thead', 'Number');   
    }
}