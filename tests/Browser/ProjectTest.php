<?php

namespace Tests\Browser;

use App\Models\Client;
use App\Models\Project;
use App\Models\Status;
use App\Models\User;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Test;
use Tests\DuskTestCase;

class ProjectTest extends DuskTestCase
{
    #[Test]
    public function it_shows_projects_on_index_page_with_navigation_links()
    {
        /* Arrange */
        $project = Project::factory()->create([
            'status_id' => Status::typeOfProject()->where('title', 'open')->first()->id,
        ]);

        /* Act */
        $this->browse(function (Browser $browser) use ($project) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->visit('/projects/')
                ->type('.dataTables_filter input', $project->title)
                ->waitForText($project->title)
                ->clickLink($project->title)
                ->assertPathIs('/projects/' . $project->external_id)
                ->waitForText($project->title);
        });

        /* Assert */
    }

    #[Test]
    public function it_can_create_a_new_project()
    {
        /* Arrange */
        $client  = Client::factory()->create();
        $contact = $client->primary_contact;
        $user    = User::factory()->create();

        /* Act */
        $this->browse(function (Browser $browser) use ($user, $client, $contact) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->visit('/projects/create')
                ->type('title', 'This is a test project title')
                ->type('.note-editable', 'This is a short comment about the lead')
                ->select('user_assigned_id', $user->id)
                ->select('client_external_id', $client->external_id)
                ->press('Create project')
                ->waitForText($user->name)
                ->assertSee($user->name)
                ->assertSee($contact->name)
                ->assertSee('This is a test project title');
        });

        /* Assert */
    }

    #[Test]
    public function it_can_create_new_task_from_project()
    {
        /* Arrange */
        $project = Project::factory()->create();

        /* Act */
        $this->browse(function (Browser $browser) use ($project) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->visit('/projects/' . $project->external_id)
                ->assertSeeLink('New task')
                ->click('#page-content-wrapper > div > div > div > div:nth-child(3) > div > div > nav > a')
                ->assertPathIs('/tasks/create/' . $project->client->external_id . '/' . $project->external_id);
        });

        /* Assert */
    }

    /**
     * Test i can comment on a project.
     */
    #[Test]
    public function it_can_add_a_new_comment_on_a_project()
    {
        /* Arrange */
        $project = Project::factory()->create();

        /* Act */
        $this->browse(function (Browser $browser) use ($project) {
            $browser->driver->executeScript('window.scrollTo(0, 600)');
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->visit('/projects/' . $project->external_id)
                ->type('.note-editable', 'This is a test comment')
                ->press('Add Comment')
                ->assertSee('This is a test comment')
                ->assertSee('Comment by: Admin');
        });

        /* Assert */
    }

    #[Test]
    public function it_can_assign_a_new_user_to_project()
    {
        /* Arrange */
        $project = Project::factory()->create();
        $user    = User::factory()->create();

        /* Act */
        $this->browse(function (Browser $browser) use ($project, $user) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->visit('/projects/' . $project->external_id)
                ->click('#assignee-user')
                ->clickLink($user->name)
                ->waitForText($user->name)
                ->assertSee($user->email);
        });

        /* Assert */
    }

    /**
     * Test i can create a new task.
     */
    #[Test]
    public function it_can_go_to_create_new_client_in_dropdown_if_no_clients_exists_from_project()
    {
        /* Arrange */
        $user = User::factory()->create();

        /* Act */
        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->visit('/projects/create')
                ->select('user_assigned_id', $user->id)
                ->select('client_external_id', 'new_client')
                ->assertPathIs('/clients/create');
        });

        /* Assert */
    }

    /**
     * Test i can close a open project.
     */
    #[Test]
    public function it_can_change_status_on_an_open_project()
    {
        /* Arrange */
        $project = Project::factory()->create();

        /* Act */
        $this->browse(function (Browser $browser) use ($project) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->visit('/projects/' . $project->external_id)
                ->assertSee($project->status->title)
                ->click('#status-text')
                ->clickLink('Pending')
                ->assertSee('Pending');
        });

        /* Assert */
    }
}
