<?php

namespace Tests\Unit\Environment;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

#[Group('environment-configuration')]
class EnvironmentConfigurationTest extends AbstractTestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_verifies_app_environment_is_testing(): void
    {
        /* Arrange */

        /* Act */
        $environment = $this->app->environment();

        /* Assert */
        $this->assertEquals('testing', $environment);
    }

    #[Test]
    public function it_verifies_cache_store_is_configured_as_array_in_test_environment(): void
    {
        /* Arrange */

        /* Act */
        $cacheDefault = config('cache.default');

        /* Assert */
        $this->assertEquals('array', $cacheDefault);
    }

    #[Test]
    public function it_verifies_cache_store_env_var_is_set_to_array(): void
    {
        /* Arrange */

        /* Act */
        $cacheStore = env('CACHE_STORE');

        /* Assert */
        $this->assertEquals('array', $cacheStore);
    }

    #[Test]
    public function it_verifies_session_driver_is_configured_in_test_environment(): void
    {
        /* Arrange */
        $validDrivers = ['array', 'file', 'cookie', 'database', 'redis', 'apc', 'memcached'];

        /* Act */
        $sessionDriver = config('session.driver');

        /* Assert */
        $this->assertNotEmpty($sessionDriver, 'Session driver must be configured');
        $this->assertContains(
            $sessionDriver,
            $validDrivers,
            'Session driver must be a valid Laravel session driver'
        );
    }

    #[Test]
    public function it_verifies_queue_connection_is_sync_in_test_environment(): void
    {
        /* Arrange */

        /* Act */
        $queueDefault = config('queue.default');

        /* Assert */
        $this->assertEquals('sync', $queueDefault);
    }

    #[Test]
    public function it_verifies_mail_mailer_does_not_send_real_emails_in_test_environment(): void
    {
        /* Arrange */
        $validMailers = ['array', 'log'];

        /* Act */
        $mailer = config('mail.default');

        /* Assert */
        $this->assertContains(
            $mailer,
            $validMailers,
            'Mail mailer must be array or log in tests to prevent real email delivery'
        );
    }

    #[Test]
    public function it_verifies_debug_mode_is_enabled_in_test_environment(): void
    {
        /* Arrange */

        /* Act */
        $debugEnabled = config('app.debug');

        /* Assert */
        $this->assertTrue($debugEnabled, 'APP_DEBUG should be true in testing environment');
    }

    #[Test]
    public function it_verifies_storage_force_local_defaults_to_true(): void
    {
        /* Arrange */

        /* Act */
        $forceLocal = config('storage.force_local');

        /* Assert */
        $this->assertTrue($forceLocal);
    }

    #[Test]
    public function it_verifies_cache_driver_env_var_is_not_set_in_test_environment(): void
    {
        /* Arrange */

        /* Act */
        $cacheDriver = env('CACHE_DRIVER');

        /* Assert */
        $this->assertNull($cacheDriver, 'CACHE_DRIVER should not be set; use CACHE_STORE instead');
    }
}
