<?php

namespace Tests\Browser;

use App\Models\Client;
use App\Models\Project;
use App\Models\Status;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ProjectTest extends DuskTestCase
{
    public function test_user_can_see_tasks_on_project_index_and_go_to_the_project_with_link()
    {
        $project = Project::factory()->create([
            'status_id' => Status::typeOfProject()->where('title', 'open')->first()->id,
        ]);

        $this->browse(function (Browser $browser) use ($project) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->visit('/projects/')
                ->type('.dataTables_filter input', $project->title)
                ->waitForText($project->title)
                ->clickLink($project->title)
                ->assertPathIs('/projects/'.$project->external_id)
                ->waitForText($project->title);
        });
    }

    public function test_i_can_create_a_new_lead()
    {
        $client = Client::factory()->create();
        $contact = $client->primary_contact;
        $user = User::factory()->create();

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
    }

    public function test_can_create_new_task_from_project()
    {
        $project = Project::factory()->create();

        $this->browse(function (Browser $browser) use ($project) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->visit('/projects/'.$project->external_id)
                ->assertSeeLink('New task')
                ->click('#page-content-wrapper > div > div > div > div:nth-child(3) > div > div > nav > a')
                ->assertPathIs('/tasks/create/'.$project->client->external_id.'/'.$project->external_id);
        });
    }

    /**
     * Test i can comment on a project
     */
    public function test_i_can_add_a_new_comment_on_a_project()
    {
        $project = Project::factory()->create();
        $this->browse(function (Browser $browser) use ($project) {
            $browser->driver->executeScript('window.scrollTo(0, 600)');
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->visit('/projects/'.$project->external_id)
                ->type('.note-editable', 'This is a test comment')
                ->press('Add Comment')
                ->assertSee('This is a test comment')
                ->assertSee('Comment by: Admin');
        });
    }

    /**
     * Test i can close a open project
     */
    public function test_i_can_change_status_on_a_open_project()
    {
        $project = Project::factory()->create();
        $this->browse(function (Browser $browser) use ($project) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->visit('/projects/'.$project->external_id)
                ->assertSee($project->status->title)
                ->click('#status-text')
                ->clickLink('Pending')
                ->assertSee('Pending');
        });
    }

    public function test_i_can_assign_a_new_user_to_project()
    {
        $project = Project::factory()->create();
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($project, $user) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->visit('/projects/'.$project->external_id)
                ->click('#assignee-user')
                ->clickLink($user->name)
                ->waitForText($user->name)
                ->assertSee($user->email);
        });
    }

    /**
     * Test i can create a new task
     */
    public function test_i_can_go_to_create_new_client_in_dropdown_if_no_clients_exists_from_project()
    {
        Client::query()->forceDelete();

        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->visit('/projects/create')
                ->select('user_assigned_id', $user->id)
                ->select('client_external_id', 'new_client')
                ->assertPathIs('/clients/create');
        });
    }
}
