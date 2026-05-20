<?php

namespace App\Console\Commands;

use App\Services\Entrust\EntrustCacheService;
use Exception;
use Illuminate\Cache\TaggableStore;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class ClearEntrustCacheCommand extends Command
{
    protected $signature = 'entrust:cache-clear
                            {--vverbose : Show detailed cache clearing information}';

    protected $description = 'Clear Entrust role and permission caches. Use this to resolve "permission denied" issues caused by stale permission data.';

    public function handle(): int
    {
        $this->info('Clearing Entrust cache...');
        $this->newLine();

        $verbose = $this->option('verbose');

        try {
            // Get cache store info for verbose output
            if ($verbose) {
                $cacheStore = Cache::getStore();
                $isTaggable = $cacheStore instanceof TaggableStore;
                $this->line('Cache driver: ' . config('cache.default'));
                $this->line('Taggable: ' . ($isTaggable ? 'Yes' : 'No'));

                if ($isTaggable) {
                    $this->line('  • Clearing tag: ' . Config::get('entrust.permission_role_table'));
                    $this->line('  • Clearing tag: ' . Config::get('entrust.role_user_table'));
                }
                $this->line('  • Flushing general cache (fallback)');
            }

            // Use the service to clear cache
            EntrustCacheService::clear();

            $this->newLine();
            $this->info('Entrust cache cleared successfully!');
            $this->line('Users with cached roles will see updated permissions on next login.');

            return Command::SUCCESS;
        } catch (Exception $e) {
            $this->error('Error clearing cache: ' . $e->getMessage());

            return Command::FAILURE;
        }
    }
}
