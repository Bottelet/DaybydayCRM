<?php

namespace App\Console\Commands;

use App\Enums\PermissionName;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\Entrust\EntrustCacheService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class UpgradeCommand extends Command
{
    protected $signature = 'daybyday:upgrade
                            {--fresh : Wipe all permission-role assignments and rebuild from scratch}';

    protected $description = 'Safely upgrade DaybydayCRM — creates missing permissions, syncs roles, verifies the DB chain. Safe to run repeatedly.';

    public function handle(): int
    {
        $this->info('🚀 Starting DaybydayCRM upgrade...');
        $this->newLine();

        if ($this->option('fresh')) {
            $this->warn('--fresh: wiping all permission_role assignments and rebuilding...');
            $this->newLine();
            $this->nukeAndRebuildPermissionRoles();
        } else {
            $createdCount = $this->ensureAllPermissionsExist();
            $this->newLine();

            $syncedCount = $this->ensureRolesHaveAllPermissions();
            $this->newLine();
        }

        $this->ensureFirstUserHasOwnerRole();
        $this->newLine();

        $this->flushEntrustCache();
        $this->newLine();

        // Always verify at the end — if this fails the user knows immediately
        $ok = $this->verifyPermissionChain();
        $this->newLine();

        if ( ! $ok) {
            $this->error('❌ Verification FAILED. Run `php artisan entrust:diagnose` for details.');

            return Command::FAILURE;
        }

        $this->info('✅ Upgrade complete and verified!');

        return Command::SUCCESS;
    }

    // ──────────────────────────────────────────────
    //  Steps
    // ──────────────────────────────────────────────

    private function ensureAllPermissionsExist(): int
    {
        $this->info('① Checking permissions...');

        $createdCount = 0;
        foreach (PermissionName::allPermissions() as $name => $data) {
            if ( ! Permission::where('name', $name)->exists()) {
                Permission::create([
                    'external_id'  => Str::uuid()->toString(),
                    'display_name' => $data['display_name'],
                    'name'         => $name,
                    'description'  => $data['description'],
                    'grouping'     => $data['grouping'],
                ]);
                $this->line("   + Created: {$name}");
                $createdCount++;
            }
        }

        $total = Permission::count();
        if ($createdCount === 0) {
            $this->line("   ✓ All {$total} permissions already exist");
        } else {
            $this->line("   ✓ Created {$createdCount} permissions ({$total} total)");
        }

        return $createdCount;
    }

    private function ensureRolesHaveAllPermissions(): int
    {
        $this->info('② Syncing permissions to privileged roles...');

        $allPermIds  = Permission::pluck('id')->toArray();
        $syncedCount = 0;

        $roles = Role::whereIn('name', ['owner', 'administrator', 'admin'])->get();

        if ($roles->isEmpty()) {
            $this->warn('   ⚠ No owner/administrator roles found — skipping');

            return 0;
        }

        foreach ($roles as $role) {
            $existing = $role->perms()->pluck('id')->toArray();
            $missing  = array_diff($allPermIds, $existing);

            if ( ! empty($missing)) {
                $role->perms()->syncWithoutDetaching($missing);
                $syncedCount += count($missing);
                $this->line('   + Added ' . count($missing) . " permissions to '{$role->name}'");
            } else {
                $this->line("   ✓ '{$role->name}' already has all " . count($allPermIds) . ' permissions');
            }
        }

        return $syncedCount;
    }

    private function ensureFirstUserHasOwnerRole(): void
    {
        $this->info('③ Checking first user → owner role...');

        $ownerRole = Role::where('name', 'owner')->first();
        if ( ! $ownerRole) {
            $this->warn('   ⚠ Owner role not found — skipping');

            return;
        }

        $firstUser = User::orderBy('id')->first();
        if ( ! $firstUser) {
            $this->warn('   ⚠ No users found — skipping');

            return;
        }

        if ( ! $firstUser->roles()->where('name', 'owner')->exists()) {
            $firstUser->roles()->syncWithoutDetaching([$ownerRole->id]);
            $this->line("   + Assigned '{$firstUser->email}' to owner role");
        } else {
            $this->line("   ✓ '{$firstUser->email}' already has owner role");
        }
    }

    private function flushEntrustCache(): void
    {
        $this->info('④ Flushing Entrust cache...');
        EntrustCacheService::clear();

        if (EntrustCacheService::isTaggable()) {
            $this->line('   ✓ Tagged caches (permission_role, role_user) flushed');
        } else {
            $driver = config('cache.default', 'unknown');
            $this->line("   ✓ Cache driver '{$driver}' does not use tags — Entrust queries DB directly on every request (no cache to flush)");
        }
    }

    /**
     * Directly verify the DB chain without going through Entrust caching.
     * Confirms: permissions exist → owner role has them → first user has owner role.
     */
    private function verifyPermissionChain(): bool
    {
        $this->info('⑤ Verifying permission chain (direct DB check)...');

        $ok = true;

        // 1. Spot-check a critical permission
        $check = 'client-create';
        if ( ! Permission::where('name', $check)->exists()) {
            $this->error("   ✗ Permission '{$check}' is missing from the permissions table");
            $ok = false;
        } else {
            $this->line("   ✓ Permission '{$check}' exists");
        }

        // 2. Owner role has it
        $ownerRole = Role::where('name', 'owner')->first();
        if ( ! $ownerRole) {
            $this->error('   ✗ Owner role does not exist');
            $ok = false;
        } elseif ( ! $ownerRole->perms()->where('name', $check)->exists()) {
            $this->error("   ✗ Owner role does NOT have '{$check}' in permission_role pivot");
            $ok = false;
        } else {
            $total    = $ownerRole->perms()->count();
            $expected = count(PermissionName::cases());
            $this->line("   ✓ Owner role has {$total}/{$expected} permissions (including '{$check}')");
        }

        // 3. First user has owner role
        $firstUser = User::orderBy('id')->first();
        if ( ! $firstUser) {
            $this->error('   ✗ No users in the database');
            $ok = false;
        } elseif ( ! $firstUser->roles()->where('name', 'owner')->exists()) {
            $this->error("   ✗ First user '{$firstUser->email}' is NOT assigned to the owner role");
            $ok = false;
        } else {
            $this->line("   ✓ User '{$firstUser->email}' has owner role");
        }

        return $ok;
    }

    /**
     * Nuclear option: wipe all permission_role pivots and rebuild from scratch.
     * Use when the pivot table is corrupted or inconsistent.
     */
    private function nukeAndRebuildPermissionRoles(): void
    {
        // 1. Ensure all permissions exist
        $this->ensureAllPermissionsExist();
        $this->newLine();

        // 2. Wipe all permission assignments for privileged roles only
        $this->info('Wiping privilege role permission assignments...');
        $roles = Role::whereIn('name', ['owner', 'administrator', 'admin'])->get();
        foreach ($roles as $role) {
            $role->perms()->detach();
            $this->line("   - Detached all permissions from '{$role->name}'");
        }
        $this->newLine();

        // 3. Re-attach all permissions
        $allPermIds = Permission::pluck('id')->toArray();
        $this->info('Re-attaching all permissions...');
        foreach ($roles as $role) {
            $role->perms()->syncWithoutDetaching($allPermIds);
            $this->line('   + Attached ' . count($allPermIds) . " permissions to '{$role->name}'");
        }
    }
}
