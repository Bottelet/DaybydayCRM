<?php

namespace Tests\Browser;

use App\Models\Client;
use App\Models\Task;
use App\Models\Status;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\User;

class TaskTest extends DuskTestCase
{
    /**
     * Test user can access task thorugh index page.
     */
    public function testUserCanSeeTasksOnTaskIndexAndGoToTheTaskWithLink()
    {
        $client = factory(Client::class)->create();
        $task = factory(Task::class)->create([
            'client_id' => $client->id,
            'status_id' => Status::typeOfTask()->whereTitle('Open')->first()->id,
            'title' => 'Lets',
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
    public function testICanSeeAllTheCorrectInformationOnTaskInfoPage()
    {
        $client = factory(Client::class)->create();
        $task = factory(Task::class)->create([
            'client_id' => $client->id,
            'status_id' => Status::typeOfTask()->whereTitle('Open')->first()->id
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
     * Test i can assign a new user to the task, and see the correct user info after new user is assigned
     */
    public function testICanAssignANewUserToTask()
    {
        $client = factory(Client::class)->create();
        $task = factory(Task::class)->create([
            'client_id' => $client->id,
            'status_id' => Status::typeOfTask()->whereTitle('Open')->first()->id
        ]);
        $user = factory(User::class)->create();

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
     * Test i can close a open task
     */
    public function testICanCloseAOpenTask()
    {
        $client = factory(Client::class)->create();
        $task = factory(Task::class)->create([
            'client_id' => $client->id,
            'status_id' => Status::typeOfTask()->whereTitle('Open')->first()->id
        ]);

        $this->browse(function (Browser $browser) use ($task) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->visit('/tasks/' . $task->external_id)
                ->assertSee($task->status->title)
                ->click('#status-text')
                ->clickLink("Pending")
                ->assertSee("Pending");
        });
    }

    /**
     * Test i can comment on a task
     */
    public function testICanAddANewCommentOnATask()
    {
        $client = factory(Client::class)->create();
        $task = factory(Task::class)->create([
            'client_id' => $client->id,
            'status_id' => Status::typeOfTask()->whereTitle('Open')->first()->id
        ]);

        $this->browse(function (Browser $browser) use ($task) {
            $browser->driver->executeScript('window.scrollTo(0, 600)');
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->visit('/tasks/' . $task->external_id)
                ->type('.note-editable', "This is a test comment")
                ->press("Add Comment")
                ->assertSee("This is a test comment")
                ->assertSee("Comment by: Admin");
        });
    }


    /**
     * Test i can add time to a task
     */
    public function testICanAddANewTimeToTask()
    {
        $client = factory(Client::class)->create();
        $task = factory(Task::class)->create([
            'client_id' => $client->id,
            'status_id' => Status::typeOfTask()->whereTitle('Open')->first()->id
        ]);

        $this->browse(function (Browser $browser) use ($task) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->visit('/tasks/' . $task->external_id)
                ->click("#time-manager")
                ->pause(200) // Wait for modal to popup
                ->type("title", "This is a test time title")
                ->type("comment", "This is a short comment about what has been made")
                ->type("price", 200)
                ->type("quantity", 4)
                ->select("type", "hours")
                ->press("Register time")
                ->waitForText("Time has been updated")
                ->assertSee("Time has been updated");
        });
    }

    /**
     * Test i can create a new task
     */
    public function testICanCreateANewTask()
    {
        $client = factory(Client::class)->create();
        $user = factory(User::class)->create();
        $contact = $client->primary_contact;

        $this->browse(function (Browser $browser) use ($user, $client, $contact) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->visit('/tasks/create')
                ->type('title', "This is a test task title")
                ->type(".note-editable", "This is a short comment about the task")
                ->select('user_assigned_id', $user->id)
                ->select('client_external_id', $client->external_id)
                ->press("Create task")
                ->waitForText("Task successfully added")
                ->assertSee($user->name)
                ->assertSee($contact->name)
                ->assertSee("This is a test task title");
        });
    }

    /**
     * Test i can create a new task
     */
    public function testICanGoToCreateNewClientInDropdownIfNoClientsExistsFromTask()
    {
        Client::query()->forceDelete();
        
        $user = factory(User::class)->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->visit('/tasks/create')
                ->select('user_assigned_id', $user->id)
                ->select('client_external_id', "new_client")
                ->assertPathIs('/clients/create');
        });
    }
}
