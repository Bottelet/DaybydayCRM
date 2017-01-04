 <?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

class ClientPageTest extends TestCase
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
	 * Test that user can see all tabs on the client page
	 */
    public function testCanSeeTabs()
    {
        $this->visit('/')
            ->seePageIs('/login')
            ->type('bottelet@flarepoint.com', 'email')
            ->type('admin', 'password')
            ->press('Login')
            ->visit('clients/' . $this->client->id)
            //tabs
            ->seeInElement('.nav-tabs', 'Tasks')
            	->seeInElement('#task', 'All tasks')
	            ->seeInElement('#task', 'Add new task')
	            ->seeInElement('#task', 'Title')
	            ->seeInElement('#task', 'Assigned user')
	            ->seeInElement('#task', 'Created at')
	            ->seeInElement('#task', 'Deadline')
            ->seeInElement('.nav-tabs', 'Leads')
            	->seeInElement('#lead', 'All leads')
	            ->seeInElement('#lead', 'Add new lead')
	            ->seeInElement('#lead', 'Title')
	            ->seeInElement('#lead', 'Assigned user')
	            ->seeInElement('#lead', 'Created at')
	            ->seeInElement('#lead', 'Deadline')
            ->seeInElement('.nav-tabs', 'Documents')
            	->seeInElement('#document', 'All documents')
	            ->seeInElement('#document', 'File')
	            ->seeInElement('#document', 'Size')
	            ->seeInElement('#document', 'Created at')
            ->seeInElement('.nav-tabs', 'Invoices')
	            ->seeInElement('#invoice', 'ID')
	            ->seeInElement('#invoice', 'Hours')
	            ->seeInElement('#invoice', 'Total amount')
	            ->seeInElement('#invoice', 'Invoice sent')
	            ->seeInElement('#invoice', 'Payment received');     	
    }
}