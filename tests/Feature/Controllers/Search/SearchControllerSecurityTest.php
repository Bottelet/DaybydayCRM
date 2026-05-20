<?php

namespace Tests\Feature\Controllers\Search;

use App\Models\Client;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

#[Group('security')]
#[Group('search-controller')]
class SearchControllerSecurityTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Client::factory()->create(['company_name' => 'Test Company']);
        Task::factory()->create(['title' => 'Test Tasks']);
        Project::factory()->create(['title' => 'Test Projects']);
        Lead::factory()->create(['title' => 'Test Leads']);
        User::factory()->create(['name' => 'Test User']);
    }

    #[Test]
    public function it_search_with_valid_type_client_returns_results()
    {
        /* Arrange */

        /* Act */
        $response = $this->json('GET', '/search/Test/client');

        /* Assert */
        $response->assertStatus(200)
            ->assertJsonStructure(['hits']);
    }

    #[Test]
    public function it_search_with_valid_type_clients_plural_returns_results()
    {
        /* Arrange */

        /* Act */
        $response = $this->json('GET', '/search/Test/clients');

        /* Assert */
        $response->assertStatus(200)
            ->assertJsonStructure(['hits']);
    }

    #[Test]
    public function it_search_with_valid_type_task_returns_results()
    {
        /* Arrange */

        /* Act */
        $response = $this->json('GET', '/search/Test/task');

        /* Assert */
        $response->assertStatus(200)
            ->assertJsonStructure(['hits']);
    }

    #[Test]
    public function it_search_with_valid_type_project_returns_results()
    {
        /* Arrange */

        /* Act */
        $response = $this->json('GET', '/search/Test/project');

        /* Assert */
        $response->assertStatus(200)
            ->assertJsonStructure(['hits']);
    }

    #[Test]
    public function it_search_with_valid_type_lead_returns_results()
    {
        /* Arrange */

        /* Act */
        $response = $this->json('GET', '/search/Test/lead');

        /* Assert */
        $response->assertStatus(200)
            ->assertJsonStructure(['hits']);
    }

    #[Test]
    public function it_search_with_valid_type_user_returns_results()
    {
        /* Arrange */

        /* Act */
        $response = $this->json('GET', '/search/Test/user');

        /* Assert */
        $response->assertStatus(200)
            ->assertJsonStructure(['hits']);
    }

    #[Test]
    public function it_search_with_invalid_type_returns_400_error()
    {
        /* Arrange */

        /* Act */
        $response = $this->json('GET', '/search/Test/InvalidType');

        /* Assert */
        $response->assertStatus(400)
            ->assertJson(['error' => 'Invalid search type']);
    }

    #[Test]
    public function it_search_prevents_arbitrary_class_instantiation()
    {
        /* Arrange */

        /* Act */
        $response = $this->json('GET', '/search/Test/Setting');

        /* Assert */
        $response->assertStatus(400)
            ->assertJson(['error' => 'Invalid search type']);
    }

    #[Test]
    public function it_search_type_is_case_insensitive()
    {
        /* Arrange */

        /* Act */
        $response = $this->json('GET', '/search/Test/CLIENT');

        /* Assert */
        $response->assertStatus(200)
            ->assertJsonStructure(['hits']);
    }

    #[Test]
    public function it_search_rejects_namespace_injection_attempts()
    {
        /* Arrange */

        /* Act */
        $response = $this->json('GET', '/search/Test/..%2F..%2FUser');

        /* Assert */
        $response->assertStatus(404);
    }

    #[Test]
    public function it_search_rejects_class_path_injection()
    {
        /* Arrange */

        /* Act */
        $response = $this->json('GET', '/search/Test/App%5CModels%5CUser');

        /* Assert */
        $response->assertStatus(400)
            ->assertJson(['error' => 'Invalid search type']);
    }
}
