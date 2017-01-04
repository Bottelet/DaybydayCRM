  <?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

class Headertest extends TestCase
{
    use DatabaseTransactions;

    public function setup()
    {
        parent::setup();
        App::setLocale('en');

        $this->createUser();
        $this->createClient();
        $this->createRole();
        $this->createTaskPermission();
        $this->createLeadPermission();
        $this->createDepartment();
	}

    /**
     * This header is reused on Leads, Tasks, clients pages.
     * Make sure we can see all the correct information
     */
	public function testPageHeader()
	{
        $this->visit('/')
            ->seePageIs('/login')
            ->type('bottelet@flarepoint.com', 'email')
            ->type('admin', 'password')
            ->press('Login')
            ->visit('clients/' . $this->client->id)
            ->see($this->client->name)
            ->seeInElement('.contactleft', $this->client->email)
            ->seeInElement('.contactleft', $this->client->primary_number)
            ->seeInElement('.contactleft', $this->client->secondary_number)
            ->seeInElement('.contactleft', $this->client->address)
            ->seeInElement('.contactleft', $this->client->zipcode)
            ->seeInElement('.contactleft', $this->client->city)
            ->seeInElement('.contactright', $this->client->companyname)
            ->seeInElement('.contactright', $this->client->vat)
            ->seeInElement('.contactright', $this->client->company_type);
	}
}