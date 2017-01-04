 <?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

class LeadPageTest extends TestCase
{
    use DatabaseTransactions;

    public function setup()
    {
        parent::setup();
        App::setLocale('en');

        $this->createUser();
        $this->createClient();
        $this->createRole();
        $this->createClientPermission();
        $this->createLeadPermission();
        $this->createDepartment();

	}
    /**
     * Test the case where description and title is.
     */
    public function testLeadsCase()
    {
        $this->createLead('open');

        $this->visit('/')
            ->seePageIs('/login')
            ->type('bottelet@flarepoint.com', 'email')
            ->type('admin', 'password')
            ->press('Login')
            ->visit('leads/' . $this->lead->id)
            ->seePageIs('leads/' . $this->lead->id)
            ->seeInElement('.panel', $this->lead->title)
            ->seeInElement('.panel', $this->lead->description);
    }

    /**
     * Test that all the correct information in the sidebar can be seen on a open leads
     */
    public function testSidebarLeadsInformation()
    {
        $this->createlead('open');

        $this->visit('/')
            ->seePageIs('/login')
            ->type('bottelet@flarepoint.com', 'email')
            ->type('admin', 'password')
            ->press('Login')
            ->visit('leads/' . $this->lead->id)
            ->seePageIs('leads/' . $this->lead->id)
            ->see('LEAD INFORMATION')
            ->seeInElement('.sidebarbox',  $this->lead->user->name)
            ->seeInElement('.sidebarbox', 'Created: ' . date('d F, Y, H:i', strtotime($this->lead->created_at)))
            ->seeInElement('.sidebarbox', 'status: Contact client')
            ->see('Assign new user')
            ->see('Complete lead'); 
    }

    /**
     * Test that all the correct information in the sidebar can be seen on a closed leads
     */
    public function testSidebarDoesntSeeAssignIfleadsIsClosed()
    {
        $this->createlead('closed');

        $this->visit('/')
            ->seePageIs('/login')
            ->type('bottelet@flarepoint.com', 'email')
            ->type('admin', 'password')
            ->press('Login')
            ->visit('leads/' . $this->lead->id)
            ->see('LEAD INFORMATION')
            ->seeInElement('.sidebarbox',  $this->lead->user->name)
            ->seeInElement('.sidebarbox', 'Created: ' . date('d F, Y, H:i', strtotime($this->lead->created_at)))
            ->seeInElement('.sidebarbox', 'Status: Completed')
            ->dontSee('Assign new user')
            ->dontSee('Complete lead'); 
    }
}