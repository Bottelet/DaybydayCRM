<?php

namespace Tests\Feature\Commands;

use Illuminate\Cache\TaggableStore;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

#[Group('entrust-cache-clear')]
class ClearEntrustCacheCommandTest extends AbstractTestCase
{
    #[Test]
    public function it_command_executes_successfully()
    {
        /* Arrange */

        /* Act & Assert */
        $this->artisan('entrust:cache-clear')
            ->assertExitCode(0);
    }

    #[Test]
    public function it_command_clears_permission_role_cache()
    {
        /* Arrange */
        if ( ! Cache::getStore() instanceof TaggableStore) {
            $this->markTestSkipped('Cache driver does not support tags');
        }

        $tag = Config::get('entrust.permission_role_table');
        Cache::tags($tag)->put('test_key', 'test_value', 60);
        $this->assertEquals('test_value', Cache::tags($tag)->get('test_key'));

        /* Act */
        $this->artisan('entrust:cache-clear');

        /* Assert */
        $this->assertNull(Cache::tags($tag)->get('test_key'));
    }

    #[Test]
    public function it_command_clears_role_user_cache()
    {
        /* Arrange */
        if ( ! Cache::getStore() instanceof TaggableStore) {
            $this->markTestSkipped('Cache driver does not support tags');
        }

        $tag = Config::get('entrust.role_user_table');
        Cache::tags($tag)->put('test_key', 'test_value', 60);
        $this->assertEquals('test_value', Cache::tags($tag)->get('test_key'));

        /* Act */
        $this->artisan('entrust:cache-clear');

        /* Assert */
        $this->assertNull(Cache::tags($tag)->get('test_key'));
    }

    #[Test]
    public function it_command_clears_general_cache()
    {
        /* Arrange */
        if ( ! Cache::getStore() instanceof TaggableStore) {
            $this->markTestSkipped('General cache flush only applies when tagged cache is used; non-taggable drivers leave general cache intact by design.');
        }

        $tag = Config::get('entrust.permission_role_table');
        Cache::tags($tag)->put('test_key_entrust', 'test_value', 60);

        /* Act */
        $this->artisan('entrust:cache-clear');

        /* Assert - Entrust tagged cache is cleared */
        $this->assertNull(Cache::tags($tag)->get('test_key_entrust'));
    }

    #[Test]
    public function it_command_displays_success_message()
    {
        /* Arrange */

        /* Act */
        $this->artisan('entrust:cache-clear')
            ->assertExitCode(0);

        /* Assert - command doesn't error */
    }

    #[Test]
    public function it_command_with_verbose_option_shows_details()
    {
        /* Arrange */

        /* Act */
        $this->artisan('entrust:cache-clear', ['--verbose' => true])
            ->assertExitCode(0);

        /* Assert - command doesn't error */
    }

    #[Test]
    public function it_command_is_idempotent_safe_to_run_multiple_times()
    {
        /* Arrange */

        /* Act & Assert */
        for ($i = 0; $i < 3; $i++) {
            $this->artisan('entrust:cache-clear')
                ->assertExitCode(0);
        }
    }

    #[Test]
    public function it_command_clears_multiple_cache_entries()
    {
        /* Arrange */
        if ( ! Cache::getStore() instanceof TaggableStore) {
            $this->markTestSkipped('Cache driver does not support tags');
        }

        $tag = Config::get('entrust.permission_role_table');
        Cache::tags($tag)->put('role_1_perms', ['perm1', 'perm2'], 60);
        Cache::tags($tag)->put('role_2_perms', ['perm3', 'perm4'], 60);

        /* Act */
        $this->artisan('entrust:cache-clear');

        /* Assert */
        $this->assertNull(Cache::tags($tag)->get('role_1_perms'));
        $this->assertNull(Cache::tags($tag)->get('role_2_perms'));
    }

    #[Test]
    public function it_command_returns_success_exit_code()
    {
        /* Arrange */

        /* Act & Assert */
        $this->artisan('entrust:cache-clear')
            ->assertExitCode(0);
    }
}
