<?php

namespace Tests\Feature\Url;

use App\Models\Client;
use App\Models\Integration;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
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

    private $task;

    private $project;

    private $client;

    /** Original forced root URL so we can restore it in tearDown */
    private string $originalRootUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalRootUrl = config('app.url', 'http://localhost');

        $this->client = Client::factory()->create();
        $this->task   = Task::factory()->create([
            'user_assigned_id' => $this->user->id,
        ]);
        $this->project = Project::factory()->create([
            'user_assigned_id' => $this->user->id,
        ]);

        // A file integration must exist so that the document-upload section
        // (which is guarded by @if($filesystem_integration)) is rendered in
        // the task/project show views.
        Integration::query()->firstOrCreate(
            ['api_type' => 'file'],
            ['name' => 'local']
        );
    }

    protected function tearDown(): void
    {
        app('url')->forceRootUrl($this->originalRootUrl);
        config(['app.url' => $this->originalRootUrl]);
        parent::tearDown();
    }

    #[Test]
    public function it_url_helper_generates_absolute_urls_with_subdirectory()
    {
        /* Arrange */
        $this->setAppUrl('http://localhost/daybydaycrm/public');

        /* Act */
        $url = url('/tasks');

        /* Assert */
        $this->assertEquals('http://localhost/daybydaycrm/public/tasks', $url);
    }

    #[Test]
    public function it_url_helper_generates_absolute_urls_at_root()
    {
        /* Arrange */
        $this->setAppUrl('http://localhost');

        /* Act */
        $url = url('/tasks');

        /* Assert */
        $this->assertEquals('http://localhost/tasks', $url);
    }

    #[Test]
    public function it_task_show_page_contains_correct_document_upload_url()
    {
        /* Arrange - capture route URL before changing the forced root */
        $routeUrl    = route('tasks.show', $this->task->external_id);
        $expectedUrl = 'http://localhost/daybydaycrm/public/add-documents/' . $this->task->external_id . '/task';
        $this->setAppUrl('http://localhost/daybydaycrm/public');

        /* Act */
        $response = $this->get($routeUrl);

        /* Assert */
        $response->assertStatus(200);
        $response->assertSee($expectedUrl, false);
    }

    #[Test]
    public function it_project_show_page_contains_correct_document_upload_url()
    {
        /* Arrange - capture route URL before changing the forced root */
        $routeUrl    = route('projects.show', $this->project->external_id);
        $expectedUrl = 'http://localhost/daybydaycrm/public/add-documents/' . $this->project->external_id . '/project';
        $this->setAppUrl('http://localhost/daybydaycrm/public');

        /* Act */
        $response = $this->get($routeUrl);

        /* Assert */
        $response->assertStatus(200);
        $response->assertSee($expectedUrl, false);
    }

    #[Test]
    public function it_products_index_contains_correct_creator_modal_url()
    {
        /* Arrange - capture route URL before changing the forced root */
        $routeUrl    = route('products.index');
        $expectedUrl = 'http://localhost/daybydaycrm/public/products/creator';
        $this->setAppUrl('http://localhost/daybydaycrm/public');

        /* Act */
        $response = $this->get($routeUrl);

        /* Assert */
        $response->assertStatus(200);
        $response->assertSee($expectedUrl, false);
    }

    #[Test]
    public function it_task_create_page_contains_correct_client_create_redirect_url()
    {
        /* Arrange - capture route URL before changing the forced root */
        $routeUrl    = route('tasks.create');
        $expectedUrl = 'http://localhost/daybydaycrm/public/clients/create';
        $this->setAppUrl('http://localhost/daybydaycrm/public');

        /* Act */
        $response = $this->get($routeUrl);

        /* Assert */
        $response->assertStatus(200);
        $response->assertSee($expectedUrl, false);
    }

    #[Test]
    public function it_project_create_page_contains_correct_client_create_redirect_url()
    {
        /* Arrange - capture route URL before changing the forced root */
        $routeUrl    = route('projects.create');
        $expectedUrl = 'http://localhost/daybydaycrm/public/clients/create';
        $this->setAppUrl('http://localhost/daybydaycrm/public');

        /* Act */
        $response = $this->get($routeUrl);

        /* Assert */
        $response->assertStatus(200);
        $response->assertSee($expectedUrl, false);
    }

    #[Test]
    public function it_lead_create_page_contains_correct_client_create_redirect_url()
    {
        /* Arrange - capture route URL before changing the forced root */
        $routeUrl    = route('leads.create');
        $expectedUrl = 'http://localhost/daybydaycrm/public/clients/create';
        $this->setAppUrl('http://localhost/daybydaycrm/public');

        /* Act */
        $response = $this->get($routeUrl);

        /* Assert */
        $response->assertStatus(200);
        $response->assertSee($expectedUrl, false);
    }

    #[Test]
    public function it_users_index_contains_correct_delete_url()
    {
        /* Arrange - capture route URL before changing the forced root */
        $routeUrl    = route('users.index');
        $expectedUrl = 'http://localhost/daybydaycrm/public/users';
        $this->setAppUrl('http://localhost/daybydaycrm/public');

        /* Act */
        $response = $this->get($routeUrl);

        /* Assert */
        $response->assertStatus(200);
        $response->assertSee($expectedUrl, false);
    }

    #[Test]
    public function it_master_layout_contains_base_url_configuration()
    {
        /* Arrange - capture route URL before changing the forced root */
        $routeUrl = route('tasks.index');
        $this->setAppUrl('http://localhost/daybydaycrm/public');

        /* Act */
        $response = $this->get($routeUrl);

        /* Assert */
        $response->assertStatus(200);
        $response->assertSee('baseUrl: "http://localhost/daybydaycrm/public"', false);
    }

    #[Test]
    public function it_master_layout_contains_base_url_configuration_at_root()
    {
        /* Arrange - capture route URL before changing the forced root */
        $routeUrl = route('tasks.index');
        $this->setAppUrl('http://localhost');

        /* Act */
        $response = $this->get($routeUrl);

        /* Assert */
        $response->assertStatus(200);
        $response->assertSee('baseUrl: "http://localhost"', false);
    }

    #[Test]
    public function it_url_generation_works_with_https_subdirectory()
    {
        /* Arrange */
        $this->setAppUrl('https://example.com/crm/public');

        /* Act */
        $url = url('/tasks');

        /* Assert */
        $this->assertEquals('https://example.com/crm/public/tasks', $url);
    }

    #[Test]
    public function it_url_generation_works_with_port_and_subdirectory()
    {
        /* Arrange */
        $this->setAppUrl('http://localhost:8080/daybydaycrm/public');

        /* Act */
        $url = url('/tasks');

        /* Assert */
        $this->assertEquals('http://localhost:8080/daybydaycrm/public/tasks', $url);
    }

    #[Test]
    public function it_master_layout_loads_js_assets_with_correct_subdirectory_path()
    {
        /* Arrange - capture route URL before changing the forced root */
        $routeUrl = route('tasks.index');
        $this->setAppUrl('http://localhost/daybydaycrm/public');

        /* Act */
        $response = $this->get($routeUrl);

        /* Assert */
        $response->assertStatus(200);
        $response->assertSee('http://localhost/daybydaycrm/public/js/jquery.min.js', false);
        $response->assertSee('http://localhost/daybydaycrm/public/js/dropzone.js', false);
    }

    #[Test]
    public function it_master_layout_loads_js_assets_at_root_installation()
    {
        /* Arrange - capture route URL before changing the forced root */
        $routeUrl = route('tasks.index');
        $this->setAppUrl('http://localhost');

        /* Act */
        $response = $this->get($routeUrl);

        /* Assert */
        $response->assertStatus(200);
        $response->assertSee('http://localhost/js/jquery.min.js', false);
        $response->assertSee('http://localhost/js/dropzone.js', false);
    }

    #[Test]
    public function it_calendar_page_loads_js_assets_with_correct_subdirectory_path()
    {
        /* Arrange - capture route URL before changing the forced root */
        $routeUrl = route('appointments.calendar');
        $this->setAppUrl('http://localhost/daybydaycrm/public');

        /* Act */
        $response = $this->get($routeUrl);

        /* Assert */
        $response->assertStatus(200);
        $response->assertSee('http://localhost/daybydaycrm/public/css/font-awesome.min.css', false);
        $response->assertSee('http://localhost/daybydaycrm/public/css/picker.classic.css', false);
    }

    #[Test]
    public function it_calendar_page_contains_base_url_configuration()
    {
        /* Arrange - capture route URL before changing the forced root */
        $routeUrl = route('appointments.calendar');
        $this->setAppUrl('http://localhost/daybydaycrm/public');

        /* Act */
        $response = $this->get($routeUrl);

        /* Assert */
        $response->assertStatus(200);
        $response->assertSee('baseUrl: "http://localhost/daybydaycrm/public"', false);
    }
}
