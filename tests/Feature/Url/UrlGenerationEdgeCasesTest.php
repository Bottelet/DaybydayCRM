<?php

namespace Tests\Feature\Url;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

/**
 * Edge case tests for URL generation in various scenarios
 *
 * This test suite covers edge cases including:
 * - Multiple subdirectory levels
 * - Special characters in URLs
 * - Different protocols and ports
 * - Trailing slashes
 */
class UrlGenerationEdgeCasesTest extends TestCase
{
    use DatabaseTransactions;
    use WithoutMiddleware;

    /** @test */
    public function url_helper_handles_multiple_subdirectory_levels()
    {
        config(['app.url' => 'http://localhost/projects/crm/public']);

        $url = url('/tasks');

        $this->assertEquals('http://localhost/projects/crm/public/tasks', $url);
    }

    /** @test */
    public function url_helper_handles_trailing_slash_in_config()
    {
        config(['app.url' => 'http://localhost/daybydaycrm/public/']);

        $url = url('/tasks');

        // Laravel's url() helper should handle trailing slashes
        $this->assertStringContainsString('/tasks', $url);
        $this->assertStringNotContainsString('//tasks', $url);
    }

    /** @test */
    public function url_helper_handles_empty_path()
    {
        config(['app.url' => 'http://localhost/daybydaycrm/public']);

        $url = url('/');

        $this->assertEquals('http://localhost/daybydaycrm/public', $url);
    }

    /** @test */
    public function url_helper_handles_path_without_leading_slash()
    {
        config(['app.url' => 'http://localhost/daybydaycrm/public']);

        $url = url('tasks');

        $this->assertEquals('http://localhost/daybydaycrm/public/tasks', $url);
    }

    /** @test */
    public function url_helper_works_with_ipv4_address()
    {
        config(['app.url' => 'http://192.168.1.1/crm']);

        $url = url('/tasks');

        $this->assertEquals('http://192.168.1.1/crm/tasks', $url);
    }

    /** @test */
    public function url_helper_works_with_localhost_and_port()
    {
        config(['app.url' => 'http://localhost:3000/crm']);

        $url = url('/tasks');

        $this->assertEquals('http://localhost:3000/crm/tasks', $url);
    }

    /** @test */
    public function url_helper_works_with_https_and_non_standard_port()
    {
        config(['app.url' => 'https://example.com:8443/crm/public']);

        $url = url('/tasks');

        $this->assertEquals('https://example.com:8443/crm/public/tasks', $url);
    }

    /** @test */
    public function url_helper_concatenation_works_correctly()
    {
        config(['app.url' => 'http://localhost/crm']);

        $externalId = '12345-abcde';
        $url = url('/add-documents/'.$externalId.'/task');

        $this->assertEquals('http://localhost/crm/add-documents/12345-abcde/task', $url);
    }

    /** @test */
    public function url_helper_handles_special_characters_in_external_id()
    {
        config(['app.url' => 'http://localhost/crm']);

        $externalId = 'abc-123_xyz';
        $url = url('/tasks/'.$externalId);

        $this->assertEquals('http://localhost/crm/tasks/abc-123_xyz', $url);
    }

    /** @test */
    public function url_helper_works_with_subdomain()
    {
        config(['app.url' => 'http://crm.example.com/public']);

        $url = url('/tasks');

        $this->assertEquals('http://crm.example.com/public/tasks', $url);
    }

    /** @test */
    public function url_helper_works_with_subdomain_and_no_path()
    {
        config(['app.url' => 'http://crm.example.com']);

        $url = url('/tasks');

        $this->assertEquals('http://crm.example.com/tasks', $url);
    }

    /** @test */
    public function url_helper_handles_query_parameters()
    {
        config(['app.url' => 'http://localhost/crm']);

        $url = url('/tasks?status=active&user=123');

        $this->assertEquals('http://localhost/crm/tasks?status=active&user=123', $url);
    }

    /** @test */
    public function url_helper_handles_fragments()
    {
        config(['app.url' => 'http://localhost/crm']);

        $url = url('/tasks#section1');

        $this->assertEquals('http://localhost/crm/tasks#section1', $url);
    }

    /** @test */
    public function base_url_config_is_available_in_javascript_context()
    {
        config(['app.url' => 'http://localhost/crm/public']);

        $response = $this->get(route('tasks.index'));

        $response->assertStatus(200);

        // Verify DayByDay.baseUrl is set
        $response->assertSee('var DayByDay', false);
        $response->assertSee('baseUrl:', false);
        $response->assertSee('http://localhost/crm/public', false);
    }

    /** @test */
    public function javascript_url_construction_matches_php_url_helper()
    {
        config(['app.url' => 'http://localhost/crm/public']);

        $phpUrl = url('/products/creator');

        // Simulate what JavaScript would construct
        $jsBaseUrl = config('app.url');
        $jsUrl = $jsBaseUrl.'/products/creator';

        $this->assertEquals($phpUrl, $jsUrl);
    }

    /** @test */
    public function url_generation_is_consistent_across_multiple_calls()
    {
        config(['app.url' => 'http://localhost/crm']);

        $url1 = url('/tasks');
        $url2 = url('/tasks');

        $this->assertEquals($url1, $url2);
    }

    /** @test */
    public function url_helper_handles_deeply_nested_paths()
    {
        config(['app.url' => 'http://localhost/crm']);

        $url = url('/api/v1/projects/123/tasks/456/documents');

        $this->assertEquals('http://localhost/crm/api/v1/projects/123/tasks/456/documents', $url);
    }

    /** @test */
    public function empty_subdirectory_path_works_as_root_installation()
    {
        config(['app.url' => 'http://localhost']);

        $url = url('/tasks');

        $this->assertEquals('http://localhost/tasks', $url);
    }

    /** @test */
    public function url_concatenation_with_variables_works_correctly()
    {
        config(['app.url' => 'http://localhost/crm']);

        $type = 'task';
        $externalId = 'abc123';

        $url = url('/'.$type.'s/'.$externalId);

        $this->assertEquals('http://localhost/crm/tasks/abc123', $url);
    }
}
