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
    public function it_user_can_see_tasks_on_project_index_and_go_to_the_project_with_link()
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
                ->clickLink($project->title);
        });

        /* Assert */
        $this->browse(function (Browser $browser) use ($project) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->assertPathIs('/projects/' . $project->external_id)
                ->waitForText($project->title);
        });
    }

    #[Test]
    public function it_i_can_create_a_new_lead()
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
                ->waitForText($user->name);
        });

        /* Assert */
        $this->browse(function (Browser $browser) use ($user, $contact) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->assertSee($user->name)
                ->assertSee($contact->name)
                ->assertSee('This is a test project title');
        });
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
                ->click('#page-content-wrapper > div > div > div > div:nth-child(3) > div > div > nav > a');
        });

        /* Assert */
        $this->browse(function (Browser $browser) use ($project) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->assertPathIs('/tasks/create/' . $project->client->external_id . '/' . $project->external_id);
        });
    }

    #[Test]
    public function it_i_can_add_a_new_comment_on_a_project()
    {
        /* Arrange */
        $project = Project::factory()->create();

        /* Act */
        $this->browse(function (Browser $browser) use ($project) {
            $browser->driver->executeScript('window.scrollTo(0, 600)');
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->visit('/projects/' . $project->external_id)
                ->type('.note-editable', 'This is a test comment')
                ->press('Add Comment');
        });

        /* Assert */
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->assertSee('This is a test comment')
                ->assertSee('Comment by: Admin');
        });
    }

    #[Test]
    public function it_i_can_change_status_on_a_open_project()
    {
        /* Arrange */
        $project = Project::factory()->create();

        /* Act */
        $this->browse(function (Browser $browser) use ($project) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->visit('/projects/' . $project->external_id)
                ->assertSee($project->status->title)
                ->click('#status-text')
                ->clickLink('Pending');
        });

        /* Assert */
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->assertSee('Pending');
        });
    }

    #[Test]
    public function it_i_can_assign_a_new_user_to_project()
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
                ->waitForText($user->name);
        });

        /* Assert */
        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->assertSee($user->email);
        });
    }

    #[Test]
    public function it_i_can_go_to_create_new_client_in_dropdown_if_no_clients_exists_from_project()
    {
        /* Arrange */
        Client::query()->forceDelete();
        $user = User::factory()->create();

        /* Act */
        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->visit('/projects/create')
                ->select('user_assigned_id', $user->id)
                ->select('client_external_id', 'new_client');
        });

        /* Assert */
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->assertPathIs('/clients/create');
        });
    }
}
