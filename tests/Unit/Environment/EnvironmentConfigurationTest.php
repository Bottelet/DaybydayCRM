<?php

namespace Tests\Unit\Environment;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Tests for configuration changes introduced in this PR:
 * - CACHE_DRIVER renamed to CACHE_STORE in .env.ci and .env.dusk.local
 * - New .env.testing file
 * - SESSION_DOMAIN=null added to .env.ci
 * - MAIL_SCHEME=null added to .env.example
 * - phpunit.xml updated to use CACHE_STORE
 */
#[Group('environment-configuration')]
class EnvironmentConfigurationTest extends AbstractTestCase
{
    use RefreshDatabase;

    // region happy_path

    /**
     * Verify the test environment is configured correctly.
     * APP_ENV must be 'testing' so framework test helpers work properly.
     */
    #[Test]
    public function it_app_environment_is_testing(): void
    {
        /** Arrange */
        // Environment configured by Laravel TestCase

        /** Act */
        $environment = $this->app->environment();

        /** Assert */
        $this->assertEquals('testing', $environment);
    }

    /**
     * Verify CACHE_STORE is the correct config key and resolves to 'array'.
     * This PR changed CACHE_DRIVER→CACHE_STORE to align with Laravel 11+.
     */
    #[Test]
    public function it_cache_store_is_configured_as_array_in_test_environment(): void
    {
        /** Arrange */
        // Configuration loaded from phpunit.xml and .env.testing

        /** Act */
        $cacheDefault = config('cache.default');

        /** Assert */
        $this->assertEquals('array', $cacheDefault);
    }

    /**
     * CACHE_STORE env var must be set to 'array' for the test suite.
     */
    #[Test]
    public function it_cache_store_env_var_is_set_to_array(): void
    {
        /** Arrange */
        // Environment variables loaded

        /** Act */
        $cacheStore = env('CACHE_STORE');

        /** Assert */
        $this->assertEquals('array', $cacheStore);
    }

    /**
     * Session driver must be configured for testing.
     */
    #[Test]
    public function it_session_driver_is_configured_in_test_environment(): void
    {
        /** Arrange */
        $validDrivers = ['array', 'file', 'cookie', 'database', 'redis', 'apc', 'memcached'];

        /** Act */
        $sessionDriver = config('session.driver');

        /** Assert */
        $this->assertNotEmpty($sessionDriver, 'Session driver must be configured');
        $this->assertContains(
            $sessionDriver,
            $validDrivers,
            'Session driver must be a valid Laravel session driver'
        );
    }

    /**
     * Queue connection must be 'sync' in test environment so queued jobs execute immediately.
     */
    #[Test]
    public function it_queue_connection_is_sync_in_test_environment(): void
    {
        /** Arrange */
        // Queue configuration loaded

        /** Act */
        $queueDefault = config('queue.default');

        /** Assert */
        $this->assertEquals('sync', $queueDefault);
    }

    /**
     * Mail mailer should be set to 'array' in tests to prevent real email delivery.
     */
    #[Test]
    public function it_mail_mailer_does_not_send_real_emails_in_test_environment(): void
    {
        /** Arrange */
        $validMailers = ['array', 'log'];

        /** Act */
        $mailer = config('mail.default');

        /** Assert */
        $this->assertContains(
            $mailer,
            $validMailers,
            'Mail mailer must be array or log in tests to prevent real email delivery'
        );
    }

    /**
     * Debug mode should be enabled in the test environment.
     */
    #[Test]
    public function it_debug_mode_is_enabled_in_test_environment(): void
    {
        /** Arrange */
        // Debug configuration loaded

        /** Act */
        $debugEnabled = config('app.debug');

        /** Assert */
        $this->assertTrue($debugEnabled, 'APP_DEBUG should be true in testing environment');
    }

    // endregion

    // region edge_cases

    /**
     * The old CACHE_DRIVER env var must NOT be used.
     * If it were set, config('cache.default') would still use CACHE_STORE.
     */
    #[Test]
    public function it_cache_driver_env_var_is_not_set_in_test_environment(): void
    {
        /** Arrange */
        // Environment variables loaded

        /** Act */
        $cacheDriver = env('CACHE_DRIVER');

        /** Assert */
        $this->assertNull($cacheDriver, 'CACHE_DRIVER should not be set; use CACHE_STORE instead');
    }

    // endregion
}
