<?php

namespace Tests\Feature\Url;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\AbstractTestCase;

/**
 * Edge case tests for URL generation in various scenarios.
 *
 * This test suite covers edge cases including:
 * - Multiple subdirectory levels
 * - Special characters in URLs
 * - Different protocols and ports
 * - Trailing slashes
 */
class UrlGenerationEdgeCasesTest extends AbstractTestCase
{
    use DatabaseTransactions;
    use WithoutMiddleware;

    /** @test */
    public function url_helper_handles_multiple_subdirectory_levels()
    {
        /* Arrange */
        config(['app.url' => 'http://localhost/projects/crm/public']);

        /* Act */
        $url = url('/tasks');

        /* Assert */
        $this->assertEquals('http://localhost/projects/crm/public/tasks', $url);
    }

    /** @test */
    public function url_helper_handles_trailing_slash_in_config()
    {
        /* Arrange */
        config(['app.url' => 'http://localhost/daybydaycrm/public/']);

        /* Act */
        $url = url('/tasks');

        /* Assert */
        $this->assertStringContainsString('/tasks', $url);
        $this->assertStringNotContainsString('//tasks', $url);
    }

    /** @test */
    public function url_helper_handles_empty_path()
    {
        /* Arrange */
        config(['app.url' => 'http://localhost/daybydaycrm/public']);

        /* Act */
        $url = url('/');

        /* Assert */
        $this->assertEquals('http://localhost/daybydaycrm/public', $url);
    }

    /** @test */
    public function url_helper_handles_path_without_leading_slash()
    {
        /* Arrange */
        config(['app.url' => 'http://localhost/daybydaycrm/public']);

        /* Act */
        $url = url('tasks');

        /* Assert */
        $this->assertEquals('http://localhost/daybydaycrm/public/tasks', $url);
    }

    /** @test */
    public function url_helper_works_with_ipv4_address()
    {
        /* Arrange */
        config(['app.url' => 'http://192.168.1.1/crm']);

        /* Act */
        $url = url('/tasks');

        /* Assert */
        $this->assertEquals('http://192.168.1.1/crm/tasks', $url);
    }

    /** @test */
    public function url_helper_works_with_localhost_and_port()
    {
        /* Arrange */
        config(['app.url' => 'http://localhost:3000/crm']);

        /* Act */
        $url = url('/tasks');

        /* Assert */
        $this->assertEquals('http://localhost:3000/crm/tasks', $url);
    }

    /** @test */
    public function url_helper_works_with_https_and_non_standard_port()
    {
        /* Arrange */
        config(['app.url' => 'https://example.com:8443/crm/public']);

        /* Act */
        $url = url('/tasks');

        /* Assert */
        $this->assertEquals('https://example.com:8443/crm/public/tasks', $url);
    }

    /** @test */
    public function url_helper_concatenation_works_correctly()
    {
        /* Arrange */
        config(['app.url' => 'http://localhost/crm']);
        $externalId = '12345-abcde';

        /* Act */
        $url = url('/add-documents/' . $externalId . '/task');

        /* Assert */
        $this->assertEquals('http://localhost/crm/add-documents/12345-abcde/task', $url);
    }

    /** @test */
    public function url_helper_handles_special_characters_in_external_id()
    {
        /* Arrange */
        config(['app.url' => 'http://localhost/crm']);
        $externalId = 'abc-123_xyz';

        /* Act */
        $url = url('/tasks/' . $externalId);

        /* Assert */
        $this->assertEquals('http://localhost/crm/tasks/abc-123_xyz', $url);
    }

    /** @test */
    public function url_helper_works_with_subdomain()
    {
        /* Arrange */
        config(['app.url' => 'http://crm.example.com/public']);

        /* Act */
        $url = url('/tasks');

        /* Assert */
        $this->assertEquals('http://crm.example.com/public/tasks', $url);
    }

    /** @test */
    public function url_helper_works_with_subdomain_and_no_path()
    {
        /* Arrange */
        config(['app.url' => 'http://crm.example.com']);

        /* Act */
        $url = url('/tasks');

        /* Assert */
        $this->assertEquals('http://crm.example.com/tasks', $url);
    }

    /** @test */
    public function url_helper_handles_query_parameters()
    {
        /* Arrange */
        config(['app.url' => 'http://localhost/crm']);

        /* Act */
        $url = url('/tasks?status=active&user=123');

        /* Assert */
        $this->assertEquals('http://localhost/crm/tasks?status=active&user=123', $url);
    }

    /** @test */
    public function url_helper_handles_fragments()
    {
        /* Arrange */
        config(['app.url' => 'http://localhost/crm']);

        /* Act */
        $url = url('/tasks#section1');

        /* Assert */
        $this->assertEquals('http://localhost/crm/tasks#section1', $url);
    }

    /** @test */
    public function base_url_config_is_available_in_javascript_context()
    {
        /* Arrange */
        config(['app.url' => 'http://localhost/crm/public']);

        /* Act */
        $response = $this->get(route('tasks.index'));

        /* Assert */
        $response->assertStatus(200);
        $response->assertSee('var DayByDay', false);
        $response->assertSee('baseUrl:', false);
        $response->assertSee('http://localhost/crm/public', false);
    }

    /** @test */
    public function javascript_url_construction_matches_php_url_helper()
    {
        /* Arrange */
        config(['app.url' => 'http://localhost/crm/public']);
        $phpUrl = url('/products/creator');
        $jsBaseUrl = config('app.url');
        $jsUrl = $jsBaseUrl . '/products/creator';

        /* Act and Assert */
        $this->assertEquals($phpUrl, $jsUrl);
    }

    /** @test */
    public function url_generation_is_consistent_across_multiple_calls()
    {
        /* Arrange */
        config(['app.url' => 'http://localhost/crm']);

        /* Act */
        $url1 = url('/tasks');
        $url2 = url('/tasks');

        /* Assert */
        $this->assertEquals($url1, $url2);
    }

    /** @test */
    public function url_helper_handles_deeply_nested_paths()
    {
        /* Arrange */
        config(['app.url' => 'http://localhost/crm']);

        /* Act */
        $url = url('/api/v1/projects/123/tasks/456/documents');

        /* Assert */
        $this->assertEquals('http://localhost/crm/api/v1/projects/123/tasks/456/documents', $url);
    }

    /** @test */
    public function empty_subdirectory_path_works_as_root_installation()
    {
        /* Arrange */
        config(['app.url' => 'http://localhost']);

        /* Act */
        $url = url('/tasks');

        /* Assert */
        $this->assertEquals('http://localhost/tasks', $url);
    }

    /** @test */
    public function url_concatenation_with_variables_works_correctly()
    {
        /* Arrange */
        config(['app.url' => 'http://localhost/crm']);
        $type = 'task';
        $externalId = 'abc123';

        /* Act */
        $url = url('/' . $type . 's/' . $externalId);

        /* Assert */
        $this->assertEquals('http://localhost/crm/tasks/abc123', $url);
    }
}
