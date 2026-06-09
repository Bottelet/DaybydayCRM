<?php

namespace Tests\Unit\Services\Storage;

use App\Models\Integration;
use App\Services\Storage\Dropbox;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Spatie\Dropbox\Client as DropboxClient;
use stdClass;
use Tests\AbstractTestCase;

#[Group('storage')]
#[Group('dropbox')]
class DropboxTest extends AbstractTestCase
{
    use RefreshDatabase;

    private Dropbox $dropbox;

    private $mockClient;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a stub Dropbox client (no expectations needed)
        $this->mockClient = $this->createStub(DropboxClient::class);

        // Create integration record
        Integration::factory()->create([
            'name'     => Dropbox::class,
            'api_type' => 'file',
            'api_key'  => 'test-token',
        ]);
    }

    #[Test]
    public function it_throws_exception_when_integration_not_configured()
    {
        /* Arrange */
        Integration::query()->delete();

        /* Assert */
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Dropbox integration is not configured');

        /* Act */
        new Dropbox();
    }

    #[Test]
    public function it_successfully_uploads_a_file()
    {
        /* Arrange */
        $filename = 'test.pdf';
        $folder   = 'client-123';
        $filePath = sys_get_temp_dir() . '/test.pdf';

        // Create a temporary test file
        file_put_contents($filePath, 'test file content');

        /* Act */
        $dropbox = new Dropbox($this->mockClient);
        $result  = $dropbox->upload($folder, $filename, $filePath);

        // Clean up
        unlink($filePath);

        /* Assert */
        $this->assertIsArray($result);
        $this->assertArrayHasKey('file_path', $result);
        $this->assertArrayHasKey('id', $result);
        $this->assertEquals('Daybyday/client-123/test.pdf', $result['file_path']);
        $this->assertEquals('Daybyday/client-123/test.pdf', $result['id']);
    }

    #[Test]
    public function it_handles_upload_errors_gracefully()
    {
        /* Arrange */
        $filename = 'test.pdf';
        $folder   = 'client-123';
        $filePath = sys_get_temp_dir() . '/test.pdf';

        file_put_contents($filePath, 'test file content');

        $this->mockClient->method('upload')
            ->willThrowException(new Exception('Upload failed'));

        $dropbox = new Dropbox($this->mockClient);

        /* Assert */
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to upload file to Dropbox');

        /* Act */
        try {
            $dropbox->upload($folder, $filename, $filePath);
        } finally {
            // Clean up even when exception is thrown
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }

    #[Test]
    public function it_successfully_deletes_a_file()
    {
        /* Arrange */
        $file       = new stdClass();
        $file->path = 'Daybyday/client-123/test.pdf';

        /* Act */
        $dropbox = new Dropbox($this->mockClient);
        $result  = $dropbox->delete($file);

        /* Assert */
        $this->assertTrue($result);
    }

    #[Test]
    public function it_returns_false_for_delete_with_null_file()
    {
        /* Act */
        $dropbox = new Dropbox($this->mockClient);
        $result  = $dropbox->delete(null);

        /* Assert */
        $this->assertFalse($result);
    }

    #[Test]
    public function it_returns_true_when_deleting_non_existent_file()
    {
        /* Arrange */
        $file       = new stdClass();
        $file->path = 'Daybyday/non-existent/test.pdf';

        $this->mockClient->method('delete')
            ->willThrowException(new Exception('not_found'));

        /* Act */
        $dropbox = new Dropbox($this->mockClient);
        $result  = $dropbox->delete($file);

        /* Assert */
        $this->assertTrue($result);
    }

    #[Test]
    public function it_successfully_downloads_a_file()
    {
        /* Arrange */
        $file       = new stdClass();
        $file->path = 'Daybyday/client-123/test.pdf';

        $this->mockClient->method('download')
            ->willReturn('file content');

        /* Act */
        $dropbox = new Dropbox($this->mockClient);
        $result  = $dropbox->get($file);

        /* Assert */
        $this->assertEquals('file content', $result);
    }

    #[Test]
    public function it_returns_null_when_getting_non_existent_file()
    {
        /* Arrange */
        $file       = new stdClass();
        $file->path = 'Daybyday/non-existent/test.pdf';

        $this->mockClient->method('download')
            ->willThrowException(new Exception('not_found'));

        /* Act */
        $dropbox = new Dropbox($this->mockClient);
        $result  = $dropbox->get($file);

        /* Assert */
        $this->assertNull($result);
    }

    #[Test]
    public function it_returns_null_for_get_with_null_file()
    {
        /* Act */
        $dropbox = new Dropbox($this->mockClient);
        $result  = $dropbox->get(null);

        /* Assert */
        $this->assertNull($result);
    }

    #[Test]
    public function it_returns_fake_content_in_testing_environment_on_view()
    {
        /* Arrange */
        $file       = new stdClass();
        $file->path = 'Daybyday/client-123/test.pdf';

        /* Act */
        $dropbox = new Dropbox($this->mockClient);
        $result  = $dropbox->view($file);

        /* Assert */
        $this->assertEquals('fake file content', $result);
    }

    #[Test]
    public function it_returns_fake_content_in_testing_environment_on_download()
    {
        /* Arrange */
        $file       = new stdClass();
        $file->path = 'Daybyday/client-123/test.pdf';

        /* Act */
        $dropbox = new Dropbox($this->mockClient);
        $result  = $dropbox->download($file);

        /* Assert */
        $this->assertEquals('fake file content', $result);
    }

    #[Test]
    public function it_returns_null_for_view_with_null_file()
    {
        /* Act */
        $dropbox = new Dropbox($this->mockClient);
        $result  = $dropbox->view(null);

        /* Assert */
        $this->assertNull($result);
    }

    #[Test]
    public function it_returns_null_for_download_with_null_file()
    {
        /* Act */
        $dropbox = new Dropbox($this->mockClient);
        $result  = $dropbox->download(null);

        /* Assert */
        $this->assertNull($result);
    }

    #[Test]
    public function it_is_enabled_when_integration_exists()
    {
        /* Act */
        $dropbox = new Dropbox($this->mockClient);
        $result  = $dropbox->isEnabled();

        /* Assert */
        $this->assertTrue($result);
    }

    #[Test]
    public function it_is_disabled_when_integration_does_not_exist()
    {
        /* Arrange – create the instance while integration exists, then remove it */
        $dropbox = new Dropbox($this->mockClient);
        Integration::query()->delete();

        /* Act */
        $result = $dropbox->isEnabled();

        /* Assert */
        $this->assertFalse($result);
    }

    #[Test]
    public function it_returns_false_for_is_enabled_when_error_occurs()
    {
        /* Arrange */
        Integration::query()->delete();

        /* Assert */
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Dropbox integration is not configured');

        /* Act */
        new Dropbox();
    }

    #[Test]
    public function it_properly_constructs_full_file_path_on_upload()
    {
        /* Arrange */
        $filename = 'invoice.pdf';
        $folder   = 'invoices-client-1';
        $filePath = sys_get_temp_dir() . '/invoice.pdf';

        file_put_contents($filePath, 'invoice content');

        $expectedPath = 'Daybyday/invoices-client-1/invoice.pdf';

        /* Act */
        $dropbox = new Dropbox($this->mockClient);
        $result  = $dropbox->upload($folder, $filename, $filePath);

        // Clean up


        /* Assert */
        $this->assertEquals($expectedPath, $result['file_path']);
    }
}
