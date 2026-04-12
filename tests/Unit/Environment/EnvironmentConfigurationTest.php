<?php

namespace Tests\Unit\Environment;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

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
    /**
     * Verify the test environment is configured correctly.
     * APP_ENV must be 'testing' so framework test helpers work properly.
     */
    #[Test]
    public function app_environment_is_testing(): void
    {
        $this->assertEquals('testing', $this->app->environment());
    }

    /**
     * Verify CACHE_STORE is the correct config key and resolves to 'array'.
     * This PR changed CACHE_DRIVER→CACHE_STORE to align with Laravel 11+.
     */
    #[Test]
    public function cache_store_is_configured_as_array_in_test_environment(): void
    {
        $this->assertEquals('array', config('cache.default'));
    }

    /**
     * The old CACHE_DRIVER env var must NOT be used.
     * If it were set, config('cache.default') would still use CACHE_STORE.
     */
    #[Test]
    public function cache_driver_env_var_is_not_set_in_test_environment(): void
    {
        $this->assertNull(env('CACHE_DRIVER'), 'CACHE_DRIVER should not be set; use CACHE_STORE instead');
    }

    /**
     * CACHE_STORE env var must be set to 'array' for the test suite.
     */
    #[Test]
    public function cache_store_env_var_is_set_to_array(): void
    {
        $this->assertEquals('array', env('CACHE_STORE'));
    }

    /**
     * Session driver must be configured for testing.
     */
    #[Test]
    public function session_driver_is_configured_in_test_environment(): void
    {
        $sessionDriver = config('session.driver');
        $this->assertNotEmpty($sessionDriver, 'Session driver must be configured');
        $this->assertContains(
            $sessionDriver,
            ['array', 'file', 'cookie', 'database', 'redis', 'apc', 'memcached'],
            'Session driver must be a valid Laravel session driver'
        );
    }

    /**
     * Queue connection must be 'sync' in test environment so queued jobs execute immediately.
     */
    #[Test]
    public function queue_connection_is_sync_in_test_environment(): void
    {
        $this->assertEquals('sync', config('queue.default'));
    }

    /**
     * Mail mailer should be set to 'array' in tests to prevent real email delivery.
     */
    #[Test]
    public function mail_mailer_does_not_send_real_emails_in_test_environment(): void
    {
        $mailer = config('mail.default');
        $this->assertContains(
            $mailer,
            ['array', 'log'],
            'Mail mailer must be array or log in tests to prevent real email delivery'
        );
    }

    /**
     * Debug mode should be enabled in the test environment.
     */
    #[Test]
    public function debug_mode_is_enabled_in_test_environment(): void
    {
        $this->assertTrue(config('app.debug'), 'APP_DEBUG should be true in testing environment');
    }
}
