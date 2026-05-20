<?php

namespace Tests\Feature\Url;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use PHPUnit\Framework\Attributes\Test;
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

    /**
     * The original forced root URL captured before each test so it can be
     * restored in tearDown, preventing URL-generator state from leaking
     * between tests.
     */
    private string $originalRootUrl;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalRootUrl = config('app.url', 'http://localhost');
    }

    protected function tearDown(): void
    {
        // Restore the URL generator to the original root so subsequent tests
        // are not affected by any changes made inside this test.
        app('url')->forceRootUrl($this->originalRootUrl);
        config(['app.url' => $this->originalRootUrl]);
        parent::tearDown();
    }

    #[Test]
    public function it_url_helper_handles_multiple_subdirectory_levels()
    {
        /* Arrange */
        $this->setAppUrl('http://localhost/projects/crm/public');

        /* Act */
        $url = url('/tasks');

        /* Assert */
        $this->assertEquals('http://localhost/projects/crm/public/tasks', $url);
    }

    #[Test]
    public function it_url_helper_handles_trailing_slash_in_config()
    {
        /* Arrange */
        $this->setAppUrl('http://localhost/daybydaycrm/public/');

        /* Act */
        $url = url('/tasks');

        /* Assert */
        $this->assertStringContainsString('/tasks', $url);
        $this->assertStringNotContainsString('//tasks', $url);
    }

    #[Test]
    public function it_url_helper_handles_empty_path()
    {
        /* Arrange */
        $this->setAppUrl('http://localhost/daybydaycrm/public');

        /* Act */
        $url = url('/');

        /* Assert */
        $this->assertEquals('http://localhost/daybydaycrm/public', $url);
    }

    #[Test]
    public function it_url_helper_handles_path_without_leading_slash()
    {
        /* Arrange */
        $this->setAppUrl('http://localhost/daybydaycrm/public');

        /* Act */
        $url = url('tasks');

        /* Assert */
        $this->assertEquals('http://localhost/daybydaycrm/public/tasks', $url);
    }

    #[Test]
    public function it_url_helper_works_with_ipv4_address()
    {
        /* Arrange */
        $this->setAppUrl('http://192.168.1.1/crm');

        /* Act */
        $url = url('/tasks');

        /* Assert */
        $this->assertEquals('http://192.168.1.1/crm/tasks', $url);
    }

    #[Test]
    public function it_url_helper_works_with_localhost_and_port()
    {
        /* Arrange */
        $this->setAppUrl('http://localhost:3000/crm');

        /* Act */
        $url = url('/tasks');

        /* Assert */
        $this->assertEquals('http://localhost:3000/crm/tasks', $url);
    }

    #[Test]
    public function it_url_helper_works_with_https_and_non_standard_port()
    {
        /* Arrange */
        $this->setAppUrl('https://example.com:8443/crm/public');

        /* Act */
        $url = url('/tasks');

        /* Assert */
        $this->assertEquals('https://example.com:8443/crm/public/tasks', $url);
    }

    #[Test]
    public function it_url_helper_concatenation_works_correctly()
    {
        /* Arrange */
        $this->setAppUrl('http://localhost/crm');
        $externalId = '12345-abcde';

        /* Act */
        $url = url('/add-documents/' . $externalId . '/task');

        /* Assert */
        $this->assertEquals('http://localhost/crm/add-documents/12345-abcde/task', $url);
    }

    #[Test]
    public function it_url_helper_handles_special_characters_in_external_id()
    {
        /* Arrange */
        $this->setAppUrl('http://localhost/crm');
        $externalId = 'abc-123_xyz';

        /* Act */
        $url = url('/tasks/' . $externalId);

        /* Assert */
        $this->assertEquals('http://localhost/crm/tasks/abc-123_xyz', $url);
    }

    #[Test]
    public function it_url_helper_works_with_subdomain()
    {
        /* Arrange */
        $this->setAppUrl('http://crm.example.com/public');

        /* Act */
        $url = url('/tasks');

        /* Assert */
        $this->assertEquals('http://crm.example.com/public/tasks', $url);
    }

    #[Test]
    public function it_url_helper_works_with_subdomain_and_no_path()
    {
        /* Arrange */
        $this->setAppUrl('http://crm.example.com');

        /* Act */
        $url = url('/tasks');

        /* Assert */
        $this->assertEquals('http://crm.example.com/tasks', $url);
    }

    #[Test]
    public function it_url_helper_handles_query_parameters()
    {
        /* Arrange */
        $this->setAppUrl('http://localhost/crm');

        /* Act */
        $url = url('/tasks?status=active&user=123');

        /* Assert */
        $this->assertEquals('http://localhost/crm/tasks?status=active&user=123', $url);
    }

    #[Test]
    public function it_url_helper_handles_fragments()
    {
        /* Arrange */
        $this->setAppUrl('http://localhost/crm');

        /* Act */
        $url = url('/tasks#section1');

        /* Assert */
        $this->assertEquals('http://localhost/crm/tasks#section1', $url);
    }

    #[Test]
    public function it_base_url_config_is_available_in_javascript_context()
    {
        /* Arrange - capture route URL before changing the forced root */
        $tasksUrl = route('tasks.index');
        $this->setAppUrl('http://localhost/crm/public');

        /* Act */
        $response = $this->get($tasksUrl);

        /* Assert */
        $response->assertStatus(200);
        $response->assertSee('var DayByDay', false);
        $response->assertSee('baseUrl:', false);
        $response->assertSee('http://localhost/crm/public', false);
    }

    #[Test]
    public function it_javascript_url_construction_matches_php_url_helper()
    {
        /* Arrange */
        $this->setAppUrl('http://localhost/crm/public');
        $phpUrl    = url('/products/creator');
        $jsBaseUrl = config('app.url');
        $jsUrl     = $jsBaseUrl . '/products/creator';

        /* Act and Assert */
        $this->assertEquals($phpUrl, $jsUrl);
    }

    #[Test]
    public function it_url_generation_is_consistent_across_multiple_calls()
    {
        /* Arrange */
        $this->setAppUrl('http://localhost/crm');

        /* Act */
        $url1 = url('/tasks');
        $url2 = url('/tasks');

        /* Assert */
        $this->assertEquals($url1, $url2);
    }

    #[Test]
    public function it_url_helper_handles_deeply_nested_paths()
    {
        /* Arrange */
        $this->setAppUrl('http://localhost/crm');

        /* Act */
        $url = url('/api/v1/projects/123/tasks/456/documents');

        /* Assert */
        $this->assertEquals('http://localhost/crm/api/v1/projects/123/tasks/456/documents', $url);
    }

    #[Test]
    public function it_works_with_empty_subdirectory_path_as_root_installation()
    {
        /* Arrange */
        $this->setAppUrl('http://localhost');

        /* Act */
        $url = url('/tasks');

        /* Assert */
        $this->assertEquals('http://localhost/tasks', $url);
    }

    #[Test]
    public function it_url_concatenation_with_variables_works_correctly()
    {
        /* Arrange */
        $this->setAppUrl('http://localhost/crm');
        $type       = 'task';
        $externalId = 'abc123';

        /* Act */
        $url = url('/' . $type . 's/' . $externalId);

        /* Assert */
        $this->assertEquals('http://localhost/crm/tasks/abc123', $url);
    }
}
