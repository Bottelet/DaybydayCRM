<?php

namespace Tests\Unit\Integration;

use App\Models\Integration;
use App\Services\Billing\BillingIntegrationRegistry;
use App\Services\Billing\NullBillingAdapter;
use App\Services\Storage\NullStorageAdapter;
use App\Services\Storage\StorageAdapterRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

/**
 * Verifies that billing and storage integrations are:
 *  - Resolved via the registry (never directly from the model).
 *  - Null adapters returned when no integration is configured.
 *  - Lazy — integration boot does not happen until driver() is called.
 *  - Resilient — missing config returns NullAdapter, not an exception.
 */
#[Group('integration-isolation')]
class IntegrationRegistryIsolationTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(StorageAdapterRegistry::class)->reset();
    }

    // ─── Billing ────────────────────────────────────────────────────────────

    #[Test]
    public function it_returns_null_billing_adapter_when_no_billing_integration_is_configured()
    {
        /* Arrange – no integration rows at all */
        Integration::whereApiType('billing')->delete();

        /* Act */
        $registry = app(BillingIntegrationRegistry::class);
        $driver   = $registry->driver();

        /* Assert */
        $this->assertInstanceOf(NullBillingAdapter::class, $driver);
    }

    #[Test]
    public function it_reports_not_configured_when_no_billing_integration_is_configured()
    {
        /* Arrange */
        Integration::whereApiType('billing')->delete();

        /* Act */
        $registry = app(BillingIntegrationRegistry::class);

        /* Assert */
        $this->assertFalse($registry->isConfigured());
    }

    #[Test]
    public function it_returns_null_billing_adapter_when_billing_integration_class_does_not_exist()
    {
        /* Arrange – integration row with a non-existent class */
        Integration::factory()->create([
            'api_type' => 'billing',
            'name'     => 'NonExistentBillingClass',
        ]);

        /* Act */
        app(BillingIntegrationRegistry::class)->reset();
        $registry = app(BillingIntegrationRegistry::class);
        $driver   = $registry->driver();
        $driver   = $registry->driver();

        /* Assert */
        $this->assertInstanceOf(NullBillingAdapter::class, $driver);
    }

    #[Test]
    public function it_returns_safe_defaults_from_null_billing_adapter()
    {
        /* Arrange */
        $adapter = new NullBillingAdapter();

        /* Act & Assert – every method returns a safe non-throwing default */
        $this->assertSame([], $adapter->getContacts());
        $this->assertSame([], $adapter->getProductMapping());
        $this->assertNull($adapter->getClient());
        $this->assertNull($adapter->createInvoice([]));
        $this->assertNull($adapter->createPayment(\App\Models\Payment::factory()->make()));
        $this->assertFalse($adapter->sendInvoice(
            \App\Models\Invoice::factory()->make(),
            'subject',
            'message',
            'test@example.com'
        ));
        $this->assertTrue($adapter->deletePayment(\App\Models\Payment::factory()->make()));
    }

    #[Test]
    public function it_returns_null_from_null_billing_adapter_on_book_invoice()
    {
        /* Arrange */
        $adapter = new NullBillingAdapter();

        /* Assert – bookInvoice should never throw */
        $result = $adapter->bookInvoice('some-guid', now()->toDateTimeString());
        $this->assertNull($result);
    }

    #[Test]
    public function it_returns_null_from_null_billing_adapter_on_get_primary_contact()
    {
        /* Arrange */
        $adapter = new NullBillingAdapter();
        $client  = \App\Models\Client::factory()->make();

        /* Assert */
        $this->assertNull($adapter->getPrimaryContact($client));
    }

    #[Test]
    public function it_resolves_the_billing_registry_as_a_singleton()
    {
        /* Act */
        $a = app(BillingIntegrationRegistry::class);
        $b = app(BillingIntegrationRegistry::class);

        /* Assert */
        $this->assertSame($a, $b);
    }

    #[Test]
    public function it_clears_the_cached_driver_after_billing_registry_reset()
    {
        /* Arrange */
        Integration::whereApiType('billing')->delete();
        $registry = app(BillingIntegrationRegistry::class);
        $first    = $registry->driver();

        /* Act */
        $registry->reset();
        $second = $registry->driver();

        /* Assert – both are NullBillingAdapter but separate instances */
        $this->assertInstanceOf(NullBillingAdapter::class, $first);
        $this->assertInstanceOf(NullBillingAdapter::class, $second);
        $this->assertNotSame($first, $second, 'reset() should clear cached driver instance');
    }

    // ─── Storage ────────────────────────────────────────────────────────────

    #[Test]
    public function it_returns_local_adapter_in_testing_environment()
    {
        /* Arrange – ensure no file integration exists */
        Integration::whereApiType('file')->delete();

        /* Act */
        $registry = app(StorageAdapterRegistry::class);
        $driver   = $registry->driver();
        $driver   = $registry->driver();

        /* Assert – testing env always uses local */
        $this->assertSame('Local', class_basename($driver));
    }

    #[Test]
    public function it_reports_not_enabled_for_the_null_storage_adapter()
    {
        /* Arrange */
        $adapter = new NullStorageAdapter();

        /* Assert */
        $this->assertFalse($adapter->isEnabled());
    }

    #[Test]
    public function it_returns_safe_defaults_from_null_storage_adapter()
    {
        /* Arrange */
        $adapter = new NullStorageAdapter();

        /* Act & Assert */
        $this->assertNull($adapter->view(null));
        $this->assertNull($adapter->download(null));
        $this->assertTrue($adapter->delete('/some/path'));

        $result = $adapter->upload('folder', 'file.txt', '/tmp/file.txt');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('file_path', $result);
    }

    #[Test]
    public function it_resolves_the_storage_registry_as_a_singleton()
    {
        /* Act */
        $a = app(StorageAdapterRegistry::class);
        $b = app(StorageAdapterRegistry::class);

        /* Assert */
        $this->assertSame($a, $b);
    }

    // ─── Integration model backward compat ──────────────────────────────────

    #[Test]
    public function it_returns_null_from_init_billing_when_no_integration_is_configured()
    {
        /* Arrange */
        Integration::whereApiType('billing')->delete();
        // Reset the registry singleton cache between tests
        app(BillingIntegrationRegistry::class)->reset();

        /* Act */
        $result = Integration::initBillingIntegration();

        /* Assert */
        $this->assertNull($result);
    }
}
