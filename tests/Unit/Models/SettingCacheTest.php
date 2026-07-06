<?php

namespace Tests\Unit\Models;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

/**
 * Regression coverage for the "serializable_classes" cache allow-list
 * (config/cache.php) introduced by the Laravel 11/12 upgrade.
 *
 * PHPUnit's default CACHE_STORE is "array" (phpunit.xml), which never
 * serializes values and so can't exercise this path — these tests force
 * the "database" store (what .env.example/.env.ci actually use) to
 * reproduce the real serialize/unserialize round-trip.
 */
class SettingCacheTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['cache.default' => 'database']);
    }

    #[Test]
    public function it_returns_a_setting_instance_on_a_cache_hit_through_a_serializing_store(): void
    {
        /* Arrange */
        Cache::forget('app_settings');
        $setting = Setting::factory()->create(['company' => 'Acme Inc']);

        /* Act */
        $onMiss = Setting::cached();
        $onHit  = Setting::cached();

        /* Assert */
        $this->assertInstanceOf(Setting::class, $onMiss);
        $this->assertInstanceOf(Setting::class, $onHit);
        $this->assertSame($setting->id, $onHit->id);
        $this->assertSame('Acme Inc', $onHit->company);
    }

    #[Test]
    public function it_keeps_the_setting_class_on_the_cache_serialization_allow_list(): void
    {
        /* Arrange */
        $allowList = config('cache.serializable_classes');

        /* Act & Assert */
        $this->assertIsArray($allowList);
        $this->assertContains(Setting::class, $allowList);
    }
}
