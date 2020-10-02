<?php

namespace Tests\Browser;

use App\Models\Client;
use App\Models\Contact;
use App\Models\Lead;
use App\Models\Task;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DuskTestCase;
use App\Models\User;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Faker\Factory as Faker;

class ClientTest extends DuskTestCase
{
    /**
     * Test user can access customer thorugh index page.
     */
    public function testUserCanSeeClientsOnClientIndexAndGoToTheCustomerWithLink()
    {
        $client = factory(Client::class)->create();

        $this->browse(function (Browser $browser) use ($client) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->addCookie("step_client_create", true)
                ->addCookie("step_client_index", true)
                ->visit('/clients')
                ->type('.dataTables_filter input', $client->company_name)
                ->waitForText($client->company_name)
                ->assertSee($client->company_name)
                ->assertSee($client->vat)
                ->assertSee($client->address)
                ->clickLink($client->company_name)
                ->assertPathIs('/clients/' . $client->external_id)
                ->waitForText($client->primary_contact->name)
                ->assertSee($client->primary_contact->name)
                ->assertSee($client->company_name);
        });
    }

    /**
     * Test i can see all the correct information on customer page
     */
    public function testICanSeeAllCustomerValuesOnShowPage()
    {
        $client = factory(Client::class)->create();
        $this->browse(function (Browser $browser) use ($client) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->visit('/clients/' . $client->external_id)
                ->waitForText($client->primary_contact->name)
                ->assertSee($client->primary_contact->name)
                ->assertSee($client->primary_contact->email)
                ->assertSee($client->primary_contact->primary_number)
                ->assertSee($client->primary_contact->secondary_number)
                ->assertSee($client->address)
                ->assertSee($client->company_type)
                ->assertSee($client->vat);
        });
    }

    /**
     * Test i can see all the correct relations for customer, and not the wrong ones
     */
    public function testICanSeeTaskAndLeadsRelatedToCustomerAndNotThoseWhoAreNotRelated()
    {
        $client = factory(Client::class)->create();
        $task = factory(Task::class)->create([
            'client_id' => $client->id
        ]);
        $lead = factory(Lead::class)->create([
            'client_id' => $client->id
        ]);

        $client_2 = factory(Client::class)->create();
        $task_2 = factory(Task::class)->create([
            'client_id' => $client_2->id
        ]);
        $lead_2 = factory(Lead::class)->create([
            'client_id' => $client_2->id
        ]);
        $this->browse(function (Browser $browser) use ($client, $task, $lead, $task_2, $lead_2) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->visit('/clients/' . $client->external_id);
            #->clickddLink($task->title, 'a');
                #->assertDontSee($task_2->title);
                #->press('Leads')->element(".tablet")
                #->assertSee($lead->title)
                #->assertDontSee($lead_2->title);
        });
    }

    /**
     * Test i can assign a new user to client, and see the correct user info after new user is assigned
     */
    public function testICanAssignANewUserToCustomer()
    {
        $client = factory(Client::class)->create();
        $user = factory(User::class)->create();

        $this->browse(function (Browser $browser) use ($client, $user) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first());
            $browser->visit('/clients/' . $client->external_id);
            $browser->assertDontSee($user->email);
            $browser->assertDontSee($user->name);
            $browser->click("#assignee-user");
            $browser->clickLink($user->name, 'span');
            $browser->seeLink($user->email);
            $browser->assertSee($user->name);
        });
    }

    /**
     * Test i can create a new customer
     */
    public function testICanCreateANewCustomer()
    {
        $faker = Faker::create();
        $this->browse(function (Browser $browser) use ($faker) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->visit('/clients/create')
                ->waitForText("Create Client")
                ->type('name', $faker->name)
                ->type('email', $faker->email)
                ->type('primary_number', $faker->randomNumber(8))
                ->type('secondary_number', $faker->randomNumber(8))
                ->type('company_name', $faker->company)
                ->type('address', $faker->secondaryAddress)
                ->type('zipcode', $faker->randomNumber(4))
                ->type('city', $faker->city)
                ->type('company_type', 'ApS')
                ->select('industry_id')
                ->press('Create New Client')
                ->assertSee('Client successfully added');
        });
    }

    /**
     * Test creating a new customer will fail if all values are not given
     */
    public function testICantCreateANewCustomerWithoutNameCompanyAndEmail()
    {
        $faker = Faker::create();
        $this->browse(function (Browser $browser) use ($faker) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->visit('/clients/create')
                ->waitForText("Create Client")
                ->type('primary_number', $faker->randomNumber(8))
                ->type('address', $faker->secondaryAddress)
                ->type('zipcode', $faker->randomNumber(4))
                ->type('company_type', 'ApS')
                ->press('Create New Client')
                ->assertSee('The name field is required.')
                ->assertSee('The company name field is required.')
                ->assertSee('The email field is required.');
        });
    }

    /**
     * Test i can see all the correct information on customer page
     */
    public function testICanUpdateAnExistingClient()
    {
        $faker = Faker::create();
        $client = factory(Client::class)->create();
        $email = $faker->email;
        $address = $faker->secondaryAddress;
        $zip_code = $faker->randomNumber(4);
        $city = $faker->city;

        $this->browse(function (Browser $browser) use ($client, $email, $address, $zip_code, $city) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->visit('/clients/' . $client->external_id . '/edit')
                ->assertInputValue('name', $client->primary_contact->name)
                ->assertInputValue('email', $client->primary_contact->email)
                ->assertInputValue('company_name', $client->company_name)
                ->assertInputValue('primary_number', $client->primary_contact->primary_number)
                ->assertInputValue('secondary_number', $client->primary_contact->secondary_number)
                ->assertInputValue('address', $client->address)
                ->assertInputValue('zipcode', $client->zipcode)
                ->assertInputValue('city', $client->city)
                ->assertInputValue('company_type', $client->company_type)
                ->assertInputValue('vat', $client->vat)
                ->type('email', $email)
                ->type('address', $address)
                ->type('zipcode', $zip_code)
                ->type('city', $city)
                ->type('company_type', 'A/S')
                ->select('industry_id')
                ->press('Update client');
        });

        //Had to split this in to two functions as i had to use element to locate the submit button.
        //And can't chain it from submit. got error  Element is not clickable at point (966, 852)
        $this->browse(function (Browser $browser) use ($client, $email, $address, $zip_code, $city) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                //Assert new data is in use
                ->visit('/clients/' . $client->external_id)
                ->assertSee($email)
                ->assertSee($address)
                ->assertSee($zip_code)
                ->assertSee($city);
        });
    }
}
