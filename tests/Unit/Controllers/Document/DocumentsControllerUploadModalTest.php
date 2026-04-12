<?php

namespace Tests\Unit\Controllers\Document;

use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Test DocumentsController upload modal view for subdirectory URL generation
 *
 * This test ensures that the upload modal generates correct URLs and properly
 * validates the type parameter for task, client, and project entities.
 */
class DocumentsControllerUploadModalTest extends TestCase
{
    use DatabaseTransactions;
    use WithoutMiddleware;

    private $task;

    private $project;

    private $client;

    protected function setUp(): void
    {
        $this->markTestSkipped('Need to revisit this test class');
        parent::setUp();

        $this->user = factory(User::class)->create();
        $this->client = factory(Client::class)->create([
            'user_assigned_id' => $this->user->id,
        ]);
        $this->task = factory(Task::class)->create([
            'user_assigned_id' => $this->user->id,
            'client_id' => $this->client->id,
        ]);
        $this->project = factory(Project::class)->create([
            'user_assigned_id' => $this->user->id,
            'client_id' => $this->client->id,
        ]);
    }

    #[Test]
    public function upload_modal_view_returns_correct_view_for_task()
    {
        $response = $this->get('/add-documents/'.$this->task->external_id.'/task');

        $response->assertStatus(200);
        $response->assertViewIs('documents._uploadFileModal');
    }

    #[Test]
    public function upload_modal_view_returns_correct_view_for_project()
    {
        $response = $this->get('/add-documents/'.$this->project->external_id.'/project');

        $response->assertStatus(200);
        $response->assertViewIs('documents._uploadFileModal');
    }

    #[Test]
    public function upload_modal_view_returns_correct_view_for_client()
    {
        $response = $this->get('/add-documents/'.$this->client->external_id.'/client');

        $response->assertStatus(200);
        $response->assertViewIs('documents._uploadFileModal');
    }

    #[Test]
    public function upload_modal_passes_correct_type_to_view_for_task()
    {
        $response = $this->get('/add-documents/'.$this->task->external_id.'/task');

        $response->assertStatus(200);
        $response->assertViewHas('type', 'task');
    }

    #[Test]
    public function upload_modal_passes_correct_type_to_view_for_project()
    {
        $response = $this->get('/add-documents/'.$this->project->external_id.'/project');

        $response->assertStatus(200);
        $response->assertViewHas('type', 'project');
    }

    #[Test]
    public function upload_modal_passes_correct_type_to_view_for_client()
    {
        $response = $this->get('/add-documents/'.$this->client->external_id.'/client');

        $response->assertStatus(200);
        $response->assertViewHas('type', 'client');
    }

    #[Test]
    public function upload_modal_contains_correct_route_map_for_task()
    {
        config(['app.url' => 'http://localhost/daybydaycrm/public']);

        $response = $this->get('/add-documents/'.$this->task->external_id.'/task');

        $response->assertStatus(200);

        // Check that the route map contains the correct mapping
        $response->assertSee("'task': 'tasks'", false);
    }

    #[Test]
    public function upload_modal_contains_correct_route_map_for_project()
    {
        config(['app.url' => 'http://localhost/daybydaycrm/public']);

        $response = $this->get('/add-documents/'.$this->project->external_id.'/project');

        $response->assertStatus(200);

        // Check that the route map contains the correct mapping
        $response->assertSee("'project': 'projects'", false);
    }

    #[Test]
    public function upload_modal_contains_correct_route_map_for_client()
    {
        config(['app.url' => 'http://localhost/daybydaycrm/public']);

        $response = $this->get('/add-documents/'.$this->client->external_id.'/client');

        $response->assertStatus(200);

        // Check that the route map contains the correct mapping
        $response->assertSee("'client': 'clients'", false);
    }

    #[Test]
    public function upload_modal_contains_base_url_in_subdirectory()
    {
        config(['app.url' => 'http://localhost/daybydaycrm/public']);

        $response = $this->get('/add-documents/'.$this->task->external_id.'/task');

        $response->assertStatus(200);

        // Check that the base URL is correctly set
        $response->assertSee('http://localhost/daybydaycrm/public', false);
    }

    #[Test]
    public function upload_modal_contains_base_url_at_root()
    {
        config(['app.url' => 'http://localhost']);

        $response = $this->get('/add-documents/'.$this->task->external_id.'/task');

        $response->assertStatus(200);

        // Check that the base URL is correctly set
        $response->assertSee('http://localhost', false);
    }

    #[Test]
    public function upload_modal_contains_fail_fast_error_handling()
    {
        $response = $this->get('/add-documents/'.$this->task->external_id.'/task');

        $response->assertStatus(200);

        // Check that the error handling exists
        $response->assertSee('if (!routeName) {', false);
        $response->assertSee("console.error('Invalid type:", false);
        $response->assertSee('return;', false);
    }

    #[Test]
    public function upload_modal_has_no_fallback_for_invalid_types()
    {
        $response = $this->get('/add-documents/'.$this->task->external_id.'/task');

        $response->assertStatus(200);

        // Ensure no fallback exists (this would be like: || type + 's')
        $response->assertDontSee("|| '{{", false);
    }
}
