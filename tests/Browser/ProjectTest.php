<?php

namespace Tests\Browser;

use App\Models\Client;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Status;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\User;

class ProjectTest extends DuskTestCase
{
    public function testUserCanSeeTasksOnProjectIndexAndGoToTheProjectWithLink()
    {
        $project = factory(Project::class)->create([
            'status_id' => Status::typeOfProject()->where('title', 'open')->first()->id
        ]);
        
        $this->browse(function (Browser $browser) use ($project) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->visit('/projects/')
                ->type('.dataTables_filter input', $project->title)
                ->waitForText($project->title)
                ->clickLink($project->title)
                ->assertPathIs('/projects/' . $project->external_id)
                ->waitForText($project->title);
        });
    }

    public function testICanCreateANewLead()
    {
        $client = factory(Client::class)->create();
        $contact = $client->primary_contact;
        $user = factory(User::class)->create();

        $this->browse(function (Browser $browser) use ($user, $client, $contact) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->visit('/projects/create')
                ->type('title', "This is a test project title")
                ->type(".note-editable", "This is a short comment about the lead")
                ->select('user_assigned_id', $user->id)
                ->select('client_external_id', $client->external_id)
                ->press("Create project")
                ->waitForText($user->name)
                ->assertSee($user->name)
                ->assertSee($contact->name)
                ->assertSee("This is a test project title");
        });
    }

    public function testCanCreateNewTaskFromProject()
    {
        $project = factory(Project::class)->create();

        $this->browse(function (Browser $browser) use ($project) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->visit('/projects/'. $project->external_id)
                ->assertSeeLink("New task")
                ->click("#page-content-wrapper > div > div > div > div:nth-child(3) > div > div > nav > a")
                ->assertPathIs("/tasks/create/" . $project->client->external_id . '/' . $project->external_id);
        });
    }

    /**
     * Test i can comment on a project
     */
    public function testICanAddANewCommentOnAProject()
    {
        $project = factory(Project::class)->create();
        $this->browse(function (Browser $browser) use ($project) {
            $browser->driver->executeScript('window.scrollTo(0, 600)');
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->visit('/projects/' . $project->external_id)
                ->type('.note-editable', "This is a test comment")
                ->press("Add Comment")
                ->assertSee("This is a test comment")
                ->assertSee("Comment by: Admin");
        });
    }

    /**
     * Test i can close a open project
     */
    public function testICanChangeStatusOnAOpenProject()
    {
        $project = factory(Project::class)->create();
        $this->browse(function (Browser $browser) use ($project) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->visit('/projects/' . $project->external_id)
                ->assertSee($project->status->title)
                ->click('#status-text')
                ->clickLink("Pending")
                ->assertSee("Pending");
        });
    }

    public function testICanAssignANewUserToProject()
    {
        $project = factory(Project::class)->create();
        $user = factory(User::class)->create();

        $this->browse(function (Browser $browser) use ($project, $user) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->visit('/projects/' . $project->external_id)
                ->click('#assignee-user')
                ->clickLink($user->name)
                ->waitForText($user->name)
                ->assertSee($user->email);
        });
    }


    /**
     * Test i can create a new task
     */
    public function testICanGoToCreateNewClientInDropdownIfNoClientsExistsFromProject()
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
