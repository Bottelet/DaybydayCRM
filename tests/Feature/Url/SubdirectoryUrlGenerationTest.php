<?php

namespace Tests\Feature\Url;

use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\AbstractTestCase;

/**
 * Test URL generation for subdirectory installations.
 *
 * This test ensures that URLs are generated correctly when the application
 * is installed in a subdirectory (e.g., http://localhost/daybydaycrm/public/)
 * instead of at the domain root.
 */
class SubdirectoryUrlGenerationTest extends AbstractTestCase
{
    use DatabaseTransactions;
    use WithoutMiddleware;

    private $task;

    private $project;

    private $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user   = factory(User::class)->create();
        $this->client = factory(Client::class)->create();
        $this->task   = factory(Task::class)->create([
            'user_assigned_id' => $this->user->id,
        ]);
        $this->project = factory(Project::class)->create([
            'user_assigned_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function url_helper_generates_absolute_urls_with_subdirectory()
    {
        /* Arrange */
        config(['app.url' => 'http://localhost/daybydaycrm/public']);

        /* Act */
        $url = url('/tasks');

        /* Assert */
        $this->assertEquals('http://localhost/daybydaycrm/public/tasks', $url);
    }

    /** @test */
    public function url_helper_generates_absolute_urls_at_root()
    {
        /* Arrange */
        config(['app.url' => 'http://localhost']);

        /* Act */
        $url = url('/tasks');

        /* Assert */
        $this->assertEquals('http://localhost/tasks', $url);
    }

    /** @test */
    public function task_show_page_contains_correct_document_upload_url()
    {
        /* Arrange */
        config(['app.url' => 'http://localhost/daybydaycrm/public']);
        $expectedUrl = 'http://localhost/daybydaycrm/public/add-documents/' . $this->task->external_id . '/task';

        /* Act */
        $response = $this->get(route('tasks.show', $this->task->external_id));

        /* Assert */
        $response->assertStatus(200);
        $response->assertSee($expectedUrl, false);
    }

    /** @test */
    public function project_show_page_contains_correct_document_upload_url()
    {
        /* Arrange */
        config(['app.url' => 'http://localhost/daybydaycrm/public']);
        $expectedUrl = 'http://localhost/daybydaycrm/public/add-documents/' . $this->project->external_id . '/project';

        /* Act */
        $response = $this->get(route('projects.show', $this->project->external_id));

        /* Assert */
        $response->assertStatus(200);
        $response->assertSee($expectedUrl, false);
    }

    /** @test */
    public function products_index_contains_correct_creator_modal_url()
    {
        /* Arrange */
        config(['app.url' => 'http://localhost/daybydaycrm/public']);
        $expectedUrl = 'http://localhost/daybydaycrm/public/products/creator';

        /* Act */
        $response = $this->get(route('products.index'));

        /* Assert */
        $response->assertStatus(200);
        $response->assertSee($expectedUrl, false);
    }

    /** @test */
    public function task_create_page_contains_correct_client_create_redirect_url()
    {
        /* Arrange */
        config(['app.url' => 'http://localhost/daybydaycrm/public']);
        $expectedUrl = 'http://localhost/daybydaycrm/public/clients/create';

        /* Act */
        $response = $this->get(route('tasks.create'));

        /* Assert */
        $response->assertStatus(200);
        $response->assertSee($expectedUrl, false);
    }

    /** @test */
    public function project_create_page_contains_correct_client_create_redirect_url()
    {
        /* Arrange */
        config(['app.url' => 'http://localhost/daybydaycrm/public']);
        $expectedUrl = 'http://localhost/daybydaycrm/public/clients/create';

        /* Act */
        $response = $this->get(route('projects.create'));

        /* Assert */
        $response->assertStatus(200);
        $response->assertSee($expectedUrl, false);
    }

    /** @test */
    public function lead_create_page_contains_correct_client_create_redirect_url()
    {
        /* Arrange */
        config(['app.url' => 'http://localhost/daybydaycrm/public']);
        $expectedUrl = 'http://localhost/daybydaycrm/public/clients/create';

        /* Act */
        $response = $this->get(route('leads.create'));

        /* Assert */
        $response->assertStatus(200);
        $response->assertSee($expectedUrl, false);
    }

    /** @test */
    public function users_index_contains_correct_delete_url()
    {
        /* Arrange */
        config(['app.url' => 'http://localhost/daybydaycrm/public']);
        $expectedUrl = 'http://localhost/daybydaycrm/public/users';

        /* Act */
        $response = $this->get(route('users.index'));

        /* Assert */
        $response->assertStatus(200);
        $response->assertSee($expectedUrl, false);
    }

    /** @test */
    public function master_layout_contains_base_url_configuration()
    {
        /* Arrange */
        config(['app.url' => 'http://localhost/daybydaycrm/public']);

        /* Act */
        $response = $this->get(route('tasks.index'));

        /* Assert */
        $response->assertStatus(200);
        $response->assertSee('baseUrl: "http://localhost/daybydaycrm/public"', false);
    }

    /** @test */
    public function master_layout_contains_base_url_configuration_at_root()
    {
        /* Arrange */
        config(['app.url' => 'http://localhost']);

        /* Act */
        $response = $this->get(route('tasks.index'));

        /* Assert */
        $response->assertStatus(200);
        $response->assertSee('baseUrl: "http://localhost"', false);
    }

    /** @test */
    public function url_generation_works_with_https_subdirectory()
    {
        /* Arrange */
        config(['app.url' => 'https://example.com/crm/public']);

        /* Act */
        $url = url('/tasks');

        /* Assert */
        $this->assertEquals('https://example.com/crm/public/tasks', $url);
    }

    /** @test */
    public function url_generation_works_with_port_and_subdirectory()
    {
        /* Arrange */
        config(['app.url' => 'http://localhost:8080/daybydaycrm/public']);

        /* Act */
        $url = url('/tasks');

        /* Assert */
        $this->assertEquals('http://localhost:8080/daybydaycrm/public/tasks', $url);
    }

    /** @test */
    public function master_layout_loads_js_assets_with_correct_subdirectory_path()
    {
        /* Arrange */
        config(['app.url' => 'http://localhost/daybydaycrm/public']);

        /* Act */
        $response = $this->get(route('tasks.index'));

        /* Assert */
        $response->assertStatus(200);
        $response->assertSee('http://localhost/daybydaycrm/public/js/manifest.js', false);
        $response->assertSee('http://localhost/daybydaycrm/public/js/vendor.js', false);
    }

    /** @test */
    public function master_layout_loads_js_assets_at_root_installation()
    {
        /* Arrange */
        config(['app.url' => 'http://localhost']);

        /* Act */
        $response = $this->get(route('tasks.index'));

        /* Assert */
        $response->assertStatus(200);
        $response->assertSee('http://localhost/js/manifest.js', false);
        $response->assertSee('http://localhost/js/vendor.js', false);
    }

    /** @test */
    public function calendar_page_loads_js_assets_with_correct_subdirectory_path()
    {
        /* Arrange */
        config(['app.url' => 'http://localhost/daybydaycrm/public']);

        /* Act */
        $response = $this->get(route('appointments.calendar'));

        /* Assert */
        $response->assertStatus(200);
        $response->assertSee('http://localhost/daybydaycrm/public/js/manifest.js', false);
        $response->assertSee('http://localhost/daybydaycrm/public/js/vendor.js', false);
    }

    /** @test */
    public function calendar_page_contains_base_url_configuration()
    {
        /* Arrange */
        config(['app.url' => 'http://localhost/daybydaycrm/public']);

        /* Act */
        $response = $this->get(route('appointments.calendar'));

        /* Assert */
        $response->assertStatus(200);
        $response->assertSee('baseUrl: "http://localhost/daybydaycrm/public"', false);
    }
}
