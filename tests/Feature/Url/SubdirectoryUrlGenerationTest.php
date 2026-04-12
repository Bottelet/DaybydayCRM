<?php

namespace Tests\Feature\Url;

use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

/**
 * Test URL generation for subdirectory installations
 *
 * This test ensures that URLs are generated correctly when the application
 * is installed in a subdirectory (e.g., http://localhost/daybydaycrm/public/)
 * instead of at the domain root.
 */
class SubdirectoryUrlGenerationTest extends TestCase
{
    use DatabaseTransactions, WithoutMiddleware;

    private $task;

    private $project;

    private $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();
        $this->client = factory(Client::class)->create();
        $this->task = factory(Task::class)->create([
            'user_assigned_id' => $this->user->id,
        ]);
        $this->project = factory(Project::class)->create([
            'user_assigned_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function url_helper_generates_absolute_urls_with_subdirectory()
    {
        // Simulate subdirectory installation
        config(['app.url' => 'http://localhost/daybydaycrm/public']);

        $url = url('/tasks');

        $this->assertEquals('http://localhost/daybydaycrm/public/tasks', $url);
    }

    /** @test */
    public function url_helper_generates_absolute_urls_at_root()
    {
        // Test root installation
        config(['app.url' => 'http://localhost']);

        $url = url('/tasks');

        $this->assertEquals('http://localhost/tasks', $url);
    }

    /** @test */
    public function task_show_page_contains_correct_document_upload_url()
    {
        config(['app.url' => 'http://localhost/daybydaycrm/public']);

        $response = $this->get(route('tasks.show', $this->task->external_id));

        $response->assertStatus(200);

        // The view should contain the correct URL for document upload
        $expectedUrl = 'http://localhost/daybydaycrm/public/add-documents/'.$this->task->external_id.'/task';
        $response->assertSee($expectedUrl, false);
    }

    /** @test */
    public function project_show_page_contains_correct_document_upload_url()
    {
        config(['app.url' => 'http://localhost/daybydaycrm/public']);

        $response = $this->get(route('projects.show', $this->project->external_id));

        $response->assertStatus(200);

        $expectedUrl = 'http://localhost/daybydaycrm/public/add-documents/'.$this->project->external_id.'/project';
        $response->assertSee($expectedUrl, false);
    }

    /** @test */
    public function products_index_contains_correct_creator_modal_url()
    {
        config(['app.url' => 'http://localhost/daybydaycrm/public']);

        $response = $this->get(route('products.index'));

        $response->assertStatus(200);

        $expectedUrl = 'http://localhost/daybydaycrm/public/products/creator';
        $response->assertSee($expectedUrl, false);
    }

    /** @test */
    public function task_create_page_contains_correct_client_create_redirect_url()
    {
        config(['app.url' => 'http://localhost/daybydaycrm/public']);

        $response = $this->get(route('tasks.create'));

        $response->assertStatus(200);

        $expectedUrl = 'http://localhost/daybydaycrm/public/clients/create';
        $response->assertSee($expectedUrl, false);
    }

    /** @test */
    public function project_create_page_contains_correct_client_create_redirect_url()
    {
        config(['app.url' => 'http://localhost/daybydaycrm/public']);

        $response = $this->get(route('projects.create'));

        $response->assertStatus(200);

        $expectedUrl = 'http://localhost/daybydaycrm/public/clients/create';
        $response->assertSee($expectedUrl, false);
    }

    /** @test */
    public function lead_create_page_contains_correct_client_create_redirect_url()
    {
        config(['app.url' => 'http://localhost/daybydaycrm/public']);

        $response = $this->get(route('leads.create'));

        $response->assertStatus(200);

        $expectedUrl = 'http://localhost/daybydaycrm/public/clients/create';
        $response->assertSee($expectedUrl, false);
    }

    /** @test */
    public function users_index_contains_correct_delete_url()
    {
        config(['app.url' => 'http://localhost/daybydaycrm/public']);

        $response = $this->get(route('users.index'));

        $response->assertStatus(200);

        $expectedUrl = 'http://localhost/daybydaycrm/public/users';
        $response->assertSee($expectedUrl, false);
    }

    /** @test */
    public function master_layout_contains_base_url_configuration()
    {
        config(['app.url' => 'http://localhost/daybydaycrm/public']);

        $response = $this->get(route('tasks.index'));

        $response->assertStatus(200);

        // Check that DayByDay.baseUrl is set correctly
        $response->assertSee('baseUrl: "http://localhost/daybydaycrm/public"', false);
    }

    /** @test */
    public function master_layout_contains_base_url_configuration_at_root()
    {
        config(['app.url' => 'http://localhost']);

        $response = $this->get(route('tasks.index'));

        $response->assertStatus(200);

        $response->assertSee('baseUrl: "http://localhost"', false);
    }

    /** @test */
    public function url_generation_works_with_https_subdirectory()
    {
        config(['app.url' => 'https://example.com/crm/public']);

        $url = url('/tasks');

        $this->assertEquals('https://example.com/crm/public/tasks', $url);
    }

    /** @test */
    public function url_generation_works_with_port_and_subdirectory()
    {
        config(['app.url' => 'http://localhost:8080/daybydaycrm/public']);

        $url = url('/tasks');

        $this->assertEquals('http://localhost:8080/daybydaycrm/public/tasks', $url);
    }

    /** @test */
    public function master_layout_loads_js_assets_with_correct_subdirectory_path()
    {
        config(['app.url' => 'http://localhost/daybydaycrm/public']);

        $response = $this->get(route('tasks.index'));

        $response->assertStatus(200);

        // JS assets should use asset() helper, not hardcoded paths
        $response->assertSee('http://localhost/daybydaycrm/public/js/manifest.js', false);
        $response->assertSee('http://localhost/daybydaycrm/public/js/vendor.js', false);
    }

    /** @test */
    public function master_layout_loads_js_assets_at_root_installation()
    {
        config(['app.url' => 'http://localhost']);

        $response = $this->get(route('tasks.index'));

        $response->assertStatus(200);

        // JS assets should work at root too
        $response->assertSee('http://localhost/js/manifest.js', false);
        $response->assertSee('http://localhost/js/vendor.js', false);
    }

    /** @test */
    public function calendar_page_loads_js_assets_with_correct_subdirectory_path()
    {
        config(['app.url' => 'http://localhost/daybydaycrm/public']);

        $response = $this->get(route('appointments.calendar'));

        $response->assertStatus(200);

        // Calendar should also load assets correctly
        $response->assertSee('http://localhost/daybydaycrm/public/js/manifest.js', false);
        $response->assertSee('http://localhost/daybydaycrm/public/js/vendor.js', false);
    }

    /** @test */
    public function calendar_page_contains_base_url_configuration()
    {
        config(['app.url' => 'http://localhost/daybydaycrm/public']);

        $response = $this->get(route('appointments.calendar'));

        $response->assertStatus(200);

        // Calendar should have DayByDay.baseUrl for axios
        $response->assertSee('baseUrl: "http://localhost/daybydaycrm/public"', false);
    }
}
