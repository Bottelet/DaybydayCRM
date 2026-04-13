<?php

namespace Tests\Browser;

use App\Models\Client;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Test;
use Tests\DuskTestCase;

class TaskTest extends DuskTestCase
{
    /**
     * Test user can access task thorugh index page.
     */
    #[Test]
    public function it_user_can_see_tasks_on_task_index_and_go_to_the_task_with_link()
    {
        $client = Client::factory()->create();
        $task   = Task::factory()->create([
            'client_id' => $client->id,
            'status_id' => Status::typeOfTask()->whereTitle('Open')->first()->id,
            'title'     => 'Lets',
        ]);
        $this->browse(function (Browser $browser) use ($task) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->visit('/tasks/')
                ->type('.dataTables_filter input', $task->title)
                ->waitForText($task->title)
                ->clickLink($task->title)
                ->assertPathIs('/tasks/' . $task->external_id)
                ->waitForText($task->title);
        });
    }

    /**
     * Test user can access task thorugh index page.
     */
    #[Test]
    public function it_i_can_see_all_the_correct_information_on_task_info_page()
    {
        $client = Client::factory()->create();
        $task   = Task::factory()->create([
            'client_id' => $client->id,
            'status_id' => Status::typeOfTask()->whereTitle('Open')->first()->id,
        ]);

        $this->browse(function (Browser $browser) use ($task) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->visit('/tasks/' . $task->external_id)
                ->waitForText($task->title)
                ->assertSee($task->description)
                ->assertsee(date(carbonDateWithText(), strtotime($task->created_at)))
                ->assertSee(date(carbonDateWithText(), strtotime($task->deadline)))
                ->assertSee('Open');
        });
    }

    /**
     * Test i can assign a new user to the task, and see the correct user info after new user is assigned.
     */
    #[Test]
    public function it_i_can_assign_a_new_user_to_task()
    {
        $client = Client::factory()->create();
        $task   = Task::factory()->create([
            'client_id' => $client->id,
            'status_id' => Status::typeOfTask()->whereTitle('Open')->first()->id,
        ]);
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($task, $user) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->visit('/tasks/' . $task->external_id)
                ->click('#assignee-user')
                ->clickLink($user->name)
                ->waitForText($user->name)
                ->assertSee($user->email);
        });
    }

    /**
     * Test i can close a open task.
     */
    #[Test]
    public function it_i_can_close_a_open_task()
    {
        $client = Client::factory()->create();
        $task   = Task::factory()->create([
            'client_id' => $client->id,
            'status_id' => Status::typeOfTask()->whereTitle('Open')->first()->id,
        ]);

        $this->browse(function (Browser $browser) use ($task) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->visit('/tasks/' . $task->external_id)
                ->assertSee($task->status->title)
                ->click('#status-text')
                ->clickLink('Pending')
                ->assertSee('Pending');
        });
    }

    /**
     * Test i can comment on a task.
     */
    #[Test]
    public function it_i_can_add_a_new_comment_on_a_task()
    {
        $client = Client::factory()->create();
        $task   = Task::factory()->create([
            'client_id' => $client->id,
            'status_id' => Status::typeOfTask()->whereTitle('Open')->first()->id,
        ]);

        $this->browse(function (Browser $browser) use ($task) {
            $browser->driver->executeScript('window.scrollTo(0, 600)');
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->visit('/tasks/' . $task->external_id)
                ->type('.note-editable', 'This is a test comment')
                ->press('Add Comment')
                ->assertSee('This is a test comment')
                ->assertSee('Comment by: Admin');
        });
    }

    /**
     * Test i can add time to a task.
     */
    #[Test]
    public function it_i_can_add_a_new_time_to_task()
    {
        $client = Client::factory()->create();
        $task   = Task::factory()->create([
            'client_id' => $client->id,
            'status_id' => Status::typeOfTask()->whereTitle('Open')->first()->id,
        ]);

        $this->browse(function (Browser $browser) use ($task) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->visit('/tasks/' . $task->external_id)
                ->click('#time-manager')
                ->pause(200) // Wait for modal to popup
                ->type('title', 'This is a test time title')
                ->type('comment', 'This is a short comment about what has been made')
                ->type('price', 200)
                ->type('quantity', 4)
                ->select('type', 'hours')
                ->press('Register time')
                ->waitForText('Time has been updated')
                ->assertSee('Time has been updated');
        });
    }

    /**
     * Test i can create a new task.
     */
    #[Test]
    public function it_i_can_create_a_new_task()
    {
        $client  = Client::factory()->create();
        $user    = User::factory()->create();
        $contact = $client->primary_contact;

        $this->browse(function (Browser $browser) use ($user, $client, $contact) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->visit('/tasks/create')
                ->type('title', 'This is a test task title')
                ->type('.note-editable', 'This is a short comment about the task')
                ->select('user_assigned_id', $user->id)
                ->select('client_external_id', $client->external_id)
                ->press('Create task')
                ->waitForText('Task successfully added')
                ->assertSee($user->name)
                ->assertSee($contact->name)
                ->assertSee('This is a test task title');
        });
    }

    /**
     * Test i can create a new task.
     */
    #[Test]
    public function it_i_can_go_to_create_new_client_in_dropdown_if_no_clients_exists_from_task()
    {
        Client::query()->forceDelete();

        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->visit('/tasks/create')
                ->select('user_assigned_id', $user->id)
                ->select('client_external_id', 'new_client')
                ->assertPathIs('/clients/create');
        });
    }
}
