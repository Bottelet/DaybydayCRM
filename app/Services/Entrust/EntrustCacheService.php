<?php

namespace App\Services\Entrust;

use Exception;
use Illuminate\Cache\TaggableStore;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

/**
 * Service to manage Entrust cache operations.
 *
 * NOTE: The database cache driver does NOT support tags, so Entrust does
 * not cache roles/permissions at all when using it (every request hits DB).
 * Flushing is only meaningful for taggable drivers like Redis or Memcached.
 */
class EntrustCacheService
{
    /**
     * Returns true if the current cache driver supports tags (Redis, Memcached).
     * The database, file, and array drivers do NOT support tags.
     */
    public static function isTaggable(): bool
    {
        return Cache::getStore() instanceof TaggableStore;
    }

    /**
     * Clear all Entrust-related caches.
     *
     * For taggable drivers (Redis/Memcached): flushes only the Entrust tag namespaces.
     * For non-taggable drivers (database/file/array): no-op, since Entrust already
     * bypasses the cache and queries the DB directly on every request.
     *
     * @return bool Whether any cache was cleared
     */
    public static function clear(): bool
    {
        try {
            if (self::isTaggable()) {
                Cache::tags(Config::get('entrust.permission_role_table'))->flush();
                Cache::tags(Config::get('entrust.role_user_table'))->flush();
            }

            // For non-taggable drivers: Entrust skips the cache entirely (direct DB queries),
            // so there is nothing to flush. Do NOT call Cache::flush() here, as that would
            // wipe unrelated application data (sessions, API responses, etc.).
            return true;
        } catch (Exception $e) {
            Log::error('Failed to clear Entrust cache: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Clear only permission-role cache.
     */
    public static function clearPermissions(): void
    {
        if (self::isTaggable()) {
            Cache::tags(Config::get('entrust.permission_role_table'))->flush();
        }
    }

    /**
     * Clear only role-user cache.
     */
    public static function clearRoles(): void
    {
        if (self::isTaggable()) {
            Cache::tags(Config::get('entrust.role_user_table'))->flush();
        }
    }
}
