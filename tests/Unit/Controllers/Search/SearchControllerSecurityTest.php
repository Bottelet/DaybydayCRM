<?php

namespace Tests\Unit\Controllers\Search;

use App\Models\Client;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[Group('security')]
#[Group('search-controller')]
class SearchControllerSecurityTest extends TestCase
{
    use DatabaseTransactions, WithoutMiddleware;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data for searching
        factory(Client::class)->create(['company_name' => 'Test Company']);
        factory(Task::class)->create(['title' => 'Test Task']);
        factory(Project::class)->create(['title' => 'Test Project']);
        factory(Lead::class)->create(['title' => 'Test Lead']);
        factory(User::class)->create(['name' => 'Test User']);
    }

    #[Test]
    public function search_with_valid_type_client_returns_results()
    {
        $response = $this->json('GET', '/search/Test/client');

        $response->assertStatus(200)
            ->assertJsonStructure(['hits']);
    }

    #[Test]
    public function search_with_valid_type_clients_plural_returns_results()
    {
        $response = $this->json('GET', '/search/Test/clients');

        $response->assertStatus(200)
            ->assertJsonStructure(['hits']);
    }

    #[Test]
    public function search_with_valid_type_task_returns_results()
    {
        $response = $this->json('GET', '/search/Test/task');

        $response->assertStatus(200)
            ->assertJsonStructure(['hits']);
    }

    #[Test]
    public function search_with_valid_type_project_returns_results()
    {
        $response = $this->json('GET', '/search/Test/project');

        $response->assertStatus(200)
            ->assertJsonStructure(['hits']);
    }

    #[Test]
    public function search_with_valid_type_lead_returns_results()
    {
        $response = $this->json('GET', '/search/Test/lead');

        $response->assertStatus(200)
            ->assertJsonStructure(['hits']);
    }

    #[Test]
    public function search_with_valid_type_user_returns_results()
    {
        $response = $this->json('GET', '/search/Test/user');

        $response->assertStatus(200)
            ->assertJsonStructure(['hits']);
    }

    #[Test]
    public function search_with_invalid_type_returns_400_error()
    {
        $response = $this->json('GET', '/search/Test/InvalidType');

        $response->assertStatus(400)
            ->assertJson(['error' => 'Invalid search type']);
    }

    #[Test]
    public function search_prevents_arbitrary_class_instantiation()
    {
        // Attempt to instantiate arbitrary classes like Setting, Role, etc.
        $response = $this->json('GET', '/search/Test/Setting');

        $response->assertStatus(400)
            ->assertJson(['error' => 'Invalid search type']);
    }

    #[Test]
    public function search_type_is_case_insensitive()
    {
        $response = $this->json('GET', '/search/Test/CLIENT');

        $response->assertStatus(200)
            ->assertJsonStructure(['hits']);
    }

    #[Test]
    public function search_rejects_namespace_injection_attempts()
    {
        // Try to inject namespace path
        $response = $this->json('GET', '/search/Test/..%2F..%2FUser');

        $response->assertStatus(400)
            ->assertJson(['error' => 'Invalid search type']);
    }

    #[Test]
    public function search_rejects_class_path_injection()
    {
        // Try to use full class path
        $response = $this->json('GET', '/search/Test/App\\Models\\User');

        $response->assertStatus(400)
            ->assertJson(['error' => 'Invalid search type']);
    }
}
