 <?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

class TaskPageTest extends TestCase
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
    public function testTaskCase()
    {
        $this->createTask('open');

        $this->visit('/')
            ->seePageIs('/login')
            ->type('bottelet@flarepoint.com', 'email')
            ->type('admin', 'password')
            ->press('Login')
            ->visit('tasks/' . $this->task->id)
            ->seePageIs('tasks/' . $this->task->id)
            ->seeInElement('.panel', $this->task->title)
            ->seeInElement('.panel', $this->task->description);
    }

    /**
     * Test that all the correct information in the sidebar can be seen on a open task
     */
    public function testSidebarTaskInformation()
    {
        $this->createTask('open');

        $this->visit('/')
            ->seePageIs('/login')
            ->type('bottelet@flarepoint.com', 'email')
            ->type('admin', 'password')
            ->press('Login')
            ->visit('tasks/' . $this->task->id)
            ->seePageIs('tasks/' . $this->task->id)
            ->see('TASK INFORMATION')
            ->seeInElement('.sidebarbox',  $this->task->user->name)
            ->seeInElement('.sidebarbox', 'Created: ' . date('d F, Y, H:i', strtotime($this->task->created_at)))
            ->seeInElement('.sidebarbox', date('d, F Y', strtotime($this->task->deadline)))
            ->seeInElement('.sidebarbox', 'status: Open')
            ->see('Assign new user')
            ->see('Close task')
            ->see('TIME MANAGMENT')
            ->see('Title')
            ->see('Time')
            ->see('Add time')
            ->see('Create Invoice'); 
    }

    /**
     * Test that all the correct information in the sidebar can be seen on a closed task
     */
    public function testSidebarDoesntSeeAssignIfTaskIsClosed()
    {
        $this->createTask('closed');

        $this->visit('/')
            ->seePageIs('/login')
            ->type('bottelet@flarepoint.com', 'email')
            ->type('admin', 'password')
            ->press('Login')
            ->visit('tasks/' . $this->task->id)
            ->see('TASK INFORMATION')
            ->seeInElement('.sidebarbox',  $this->task->user->name)
            ->seeInElement('.sidebarbox', 'Created: ' . date('d F, Y, H:i', strtotime($this->task->created_at)))
            ->seeInElement('.sidebarbox', date('d, F Y', strtotime($this->task->deadline)))
            ->seeInElement('.sidebarbox', 'Status: Closed')
            ->dontSee('Assign new user')
            ->dontSee('Close task')
            ->see('TIME MANAGMENT')
            ->see('Title')
            ->see('Time')
            ->see('Add time')
            ->see('Create Invoice'); 
    }

    /**
     * Test that all the correct information in the sidebar can be seen on a closed task
     */
    //TODO Button add time can't be found atm
    /*public function testTimeInsertion()
    {
        $this->createTask('open');

        $this->visit('/')
            ->seePageIs('/login')
            ->type('bottelet@flarepoint.com', 'email')
            ->type('admin', 'password')
            ->press('Login')
            ->visit('tasks/' . $this->task->id)
            ->see('Add time')
            ->click('Add time')
            ->type('Code', 'title')
            ->type('400', 'value')
            ->type('3', 'time')
            ->press('Register Time');
    }*/
}