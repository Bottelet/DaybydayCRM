<?php

namespace Tests\Browser;

use App\Models\Client;
use App\Models\Lead;
use App\Models\Status;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\User;

class LeadTest extends DuskTestCase
{
    /**
     * Test user can access lead thorugh index page.
     */
    public function testUserCanSeeLeadsOnLeadIndexAndGoToTheLeadWithLink()
    {
        $client = factory(Client::class)->create();
        $lead = factory(Lead::class)->create([
            'client_id' => $client->id,
            'status_id' => Status::typeOfLead()->whereTitle('Open')->first()->id
        ]);
        $this->browse(function (Browser $browser) use ($lead) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->visit('/leads')
                ->type('.dataTables_filter input', $lead->title)
                ->waitForText($lead->title)
                ->clickLink($lead->title)
                ->assertPathIs('/leads/' . $lead->external_id)
                ->waitForText($lead->title);
        });
    }

    /**
     * Test user can access lead thorugh index page.
     */
    public function testICanSeeAllTheCorrectInformationOnLeadInfoPage()
    {
        $client = factory(Client::class)->create();
        $lead = factory(Lead::class)->create([
            'client_id' => $client->id
        ]);

        $this->browse(function (Browser $browser) use ($lead) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->visit('/leads/' . $lead->external_id)
                ->waitForText($lead->title)
                ->assertSee($lead->description)
                ->assertsee(date(carbonFullDateWithText(), strtotime($lead->created_at)))
                ->assertSee(date(carbonFullDateWithText(), strtotime($lead->deadline)))
                ->assertSee($lead->status->title);
        });
    }

    /**
     * Test i can assign a new user to the lead, and see the correct user info after new user is assigned
     */
    public function testICanAssignANewUserToLead()
    {
        $client = factory(Client::class)->create();
        $lead = factory(Lead::class)->create([
            'client_id' => $client->id
        ]);
        $user = factory(User::class)->create();

        $this->browse(function (Browser $browser) use ($lead, $user) {
            $browser->driver->executeScript('window.scrollTo(0, 500)');
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->visit('/leads/' . $lead->external_id)
                ->click('#assignee-user')
                ->clickLink($user->name)
                ->waitForText($user->name)
                ->assertSee($user->email);
        });
    }

    /**
     * Test i can close a open lead
     */
    public function testICanChangeLeadStatus()
    {
        $client = factory(Client::class)->create();
        $lead = factory(Lead::class)->create([
            'client_id' => $client->id,
            'status_id' => Status::typeOfLead()->first()->id
        ]);
        $this->browse(function (Browser $browser) use ($lead) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->visit('/leads/' . $lead->external_id)
                ->assertSee($lead->status->title)
                ->click('#status-text')
                ->clickLink("Pending")
                ->assertSee("Pending");
        });
    }

    /**
     * Test i can comment on a lead
     */
    public function testICanAddANewCommentOnALead()
    {
        $client = factory(Client::class)->create();
        $lead = factory(Lead::class)->create([
            'client_id' => $client->id
        ]);

        $this->browse(function (Browser $browser) use ($lead) {
            $browser->driver->executeScript('window.scrollTo(0, 500)');
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->visit('/leads/' . $lead->external_id)
                ->type('.note-editable', "This is a test comment")
                ->press("Add Comment")
                ->assertSee("This is a test comment")
                ->assertSee("Comment by: Admin");
        });
    }

    /**
     * Test i can create a new task
     */
    public function testICanCreateANewLead()
    {
        $client = factory(Client::class)->create();
        $contact = $client->primary_contact;
        $user = factory(User::class)->create();

        $this->browse(function (Browser $browser) use ($user, $client, $contact) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->visit('/leads/create')
                ->type('title', "This is a test lead title")
                ->type(".note-editable", "This is a short comment about the lead")
                ->select('user_assigned_id', $user->id)
                ->select('client_external_id', $client->external_id)
                ->press("Create lead")
                ->assertSee($user->name)
                ->assertSee($contact->name)
                ->assertSee("This is a test lead title");
        });
    }

    /**
     * Test i can create a new task
     */
    public function testICanGoToCreateNewClientInDropdownIfNoClientsExistsFromLead()
    {
        Client::query()->forceDelete();

        $user = factory(User::class)->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->visit('/projects/create')
                ->select('user_assigned_id', $user->id)
                ->select('client_external_id', "new_client")
                ->assertPathIs('/clients/create');
        });
    }
}
