<?php
namespace Tests\Unit\Controllers\Task;

use App\Models\Contact;
use App\Models\Project;
use App\Models\Status;
use App\Models\Task;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Client;
use App\Models\User;
use App\Models\Industry;

use Ramsey\Uuid\Uuid;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TasksControllerTest extends TestCase
{
    use DatabaseTransactions, WithoutMiddleware;

    private $client;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();
        $this->client = factory(Client::class)->create();
    }

    /** @test **/
    public function can_create_task()
    {
        $response = $this->json('POST', route('tasks.store'), [
                'title' => 'Task test',
                'description' => 'This is a description',
                'status_id' => factory(Status::class)->create(['source_type' => Task::class])->id,
                'user_assigned_id' => $this->user->id,
                'user_created_id' => $this->user->id,
                'client_external_id' => $this->client->external_id,
                'deadline' => '2020-01-01',
        ]);

        $tasks = Task::where('user_assigned_id', $this->user->id);

        $this->assertCount(1, $tasks->get());
        $this->assertEquals($response->getData()->task_external_id, $tasks->first()->external_id);
    }

    /** @test **/
    public function can_add_project_on_task()
    {
        $project = factory(Project::class)->create();
        $task = factory(Task::class)->create();

        $this->assertNull($task->project_id);
        $response = $this->json('POST', route('tasks.update.project', $task->external_id), [
           'project_external_id' => $project->external_id
        ]);

        $this->assertNotNull($task->refresh()->project_id);
    }

    /** @test **/
    public function can_update_assignee()
    {
        $task = factory(Task::class)->create();
        $this->assertNotEquals($task->user_assigned_id, $this->user->id);

        $response = $this->json('PATCH', route('task.update.assignee', $task->external_id), [
            'user_assigned_id' => $this->user->id
        ]);

        $this->assertEquals($task->refresh()->user_assigned_id, $this->user->id);
    }

    /** @test **/
    public function can_update_status()
    {
        $task = factory(Task::class)->create();
        $status = factory(Status::class)->create();

        $this->assertNotEquals($task->status_id, $status->id);

        $response = $this->json('PATCH', route('task.update.status', $task->external_id), [
            'status_id' => $status->id
        ]);

        $this->assertEquals($task->refresh()->status_id, $status->id);
    }

    /** @test */
    public function can_update_deadline_for_task()
    {
        $task = factory(Task::class)->create();

        $response = $this->json('PATCH', route('task.update.deadline', $task->external_id), [
            'deadline_date' => '2020-08-06',
            'deadline_time' => '00:00',
        ]);

        $this->assertEquals(Carbon::parse('2020-08-06')->toDate(), $task->refresh()->deadline->toDate());
    }

    /** @test */
    public function can_list_tasks()
    {
        factory(Task::class)->create();

        $error = $this->json('GET', route('tasks.data'))
            ->assertSuccessful()
            ->json('error');
        $this->assertNull($error);
    }
}
