 <?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

class FormValidationErrorClientTest extends TestCase
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
    }

    /**
     * Validation for name on client create form
     */
    public function testMissingName()
    {
        $this->visit('/')
            ->seePageIs('/login')
            ->type('bottelet@flarepoint.com', 'email')
            ->type('admin', 'password')
            ->press('Login')
            ->see('Clients')
            ->click('New Client')
            ->seePageIs('/clients/create')
            ->type($this->faker->email, 'email')
            ->type($this->faker->address, 'address')
            ->type($this->faker->company('name'), 'company_name')
            ->select($this->faker->numberBetween($min = 1, $max = 25), 'industry_id')
            ->select(1, 'user_id')
            ->press('Create New Client')
            ->see('The name field is required.')
            ->seePageIs('/clients/create');
    }

    /**
     * Validation for email on client create form
     */
    public function testMissingEmail()
    {
        $this->visit('/')
            ->seePageIs('/login')
            ->type('bottelet@flarepoint.com', 'email')
            ->type('admin', 'password')
            ->press('Login')
            ->see('Clients')
            ->click('New Client')
            ->seePageIs('/clients/create')
            ->type($this->faker->name, 'name')
            ->type($this->faker->address, 'address')
            ->type($this->faker->company('name'), 'company_name')
            ->select($this->faker->numberBetween($min = 1, $max = 25), 'industry_id')
            ->select(1, 'user_id')
            ->press('Create New Client')
            ->see('The email field is required.')
            ->seePageIs('/clients/create');
    }

    /**
     * Validation for company on client create form
     */
    public function testMissingCompanyName()
    {
        $this->visit('/')
            ->seePageIs('/login')
            ->type('bottelet@flarepoint.com', 'email')
            ->type('admin', 'password')
            ->press('Login')
            ->see('Clients')
            ->click('New Client')
            ->seePageIs('/clients/create')
            ->type($this->faker->name, 'name')
            ->type($this->faker->address, 'address')
            ->type($this->faker->email, 'email')
            ->select($this->faker->numberBetween($min = 1, $max = 25), 'industry_id')
            ->select(1, 'user_id')
            ->press('Create New Client')
            ->see('The company name field is required.')
            ->seePageIs('/clients/create');
    }
}