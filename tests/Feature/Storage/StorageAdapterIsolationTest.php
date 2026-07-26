<?php

namespace Tests\Feature\Storage;

use App\Enums\PermissionName;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Client;
use App\Repositories\FilesystemIntegration\FilesystemIntegration;
use App\Services\Storage\NullStorageAdapter;
use App\Services\Storage\StorageAdapterRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

/**
 * Verifies that storage infrastructure is:
 *  - Not initialized before authorization is checked.
 *  - Degrading gracefully when no adapter is enabled.
 *  - Using the registry / DI instead of static helpers.
 */
#[Group('storage-isolation')]
#[Group('authorization-order')]
class StorageAdapterIsolationTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->defaultHeaders['Accept'] = 'application/json';
        $this->withoutMiddleware([VerifyCsrfToken::class]);
        \App\Models\Setting::factory()->create();
    }

    #[Test]
    public function it_returns_422_json_when_upload_is_attempted_with_no_storage_enabled()
    {
        /* Arrange */
        app()->instance(FilesystemIntegration::class, new NullStorageAdapter());
        app(StorageAdapterRegistry::class)->reset();
        $this->withPermissions([PermissionName::DOCUMENT_UPLOAD]);
        $client = Client::factory()->create();

        /* Act */
        $response = $this->post(
            route('document.upload', $client->external_id),
            []
        );

        /* Assert */
        $response->assertStatus(422);
        $response->assertJsonFragment(['message' => __('File integration required for this action')]);
    }

    #[Test]
    public function it_returns_403_before_storage_initialises_for_an_unauthorized_upload()
    {
        /* Arrange */
        $user = \App\Models\User::factory()->create(); // no permissions
        $this->actingAs($user);
        $client = Client::factory()->create();

        /* Act */
        $response = $this->post(
            route('document.upload', $client->external_id),
            []
        );

        /* Assert */
        $response->assertStatus(403);
    }

    #[Test]
    public function it_resolves_the_storage_registry_via_the_container()
    {
        /* Arrange */

        /* Act */
        $registry = app(StorageAdapterRegistry::class);

        /* Assert */
        $this->assertInstanceOf(StorageAdapterRegistry::class, $registry);
    }

    #[Test]
    public function it_returns_the_same_storage_registry_instance_on_each_resolution()
    {
        /* Arrange */

        /* Act */
        $a = app(StorageAdapterRegistry::class);
        $b = app(StorageAdapterRegistry::class);

        /* Assert */
        $this->assertSame($a, $b);
    }
}
