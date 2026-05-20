<?php

namespace App\Console\Commands;

use App\Enums\PermissionName;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\Entrust\EntrustCacheService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class DiagnosePermissionsCommand extends Command
{
    protected $signature = 'entrust:diagnose
                            {--user= : User ID or email to diagnose (defaults to first user)}
                            {--fix   : Automatically fix any detected issues}';

    protected $description = 'Diagnose and optionally fix Entrust permission problems (missing permissions, missing role assignments, stale cache).';

    private int $issues = 0;

    public function handle(): int
    {
        $this->info('🔍 Diagnosing Entrust permission chain...');
        $this->newLine();

        // --- Step 1: Permissions in DB vs enum ---
        $this->checkPermissions();

        // --- Step 2: Owner/Admin roles exist ---
        $this->checkRoles();

        // --- Step 3: Owner/Admin roles have all permissions ---
        $this->checkRolePermissions();

        // --- Step 4: User → role check ---
        $this->checkUserRole();

        // --- Summary ---
        $this->newLine();
        if ($this->issues === 0) {
            $this->info('✅ No issues found! If you still cannot access /create routes, clear the cache:');
            $this->line('   php artisan entrust:cache-clear');
        } else {
            $this->warn("⚠  Found {$this->issues} issue(s).");

            if ($this->option('fix')) {
                $this->newLine();
                $this->info('🔧 Running fixes...');
                $this->runFixes();
            } else {
                $this->newLine();
                $this->line('Run with <comment>--fix</comment> to automatically resolve all issues:');
                $this->line('   php artisan entrust:diagnose --fix');
            }
        }

        return Command::SUCCESS;
    }

    private function checkPermissions(): void
    {
        $this->line('<fg=cyan>① Permissions (enum vs database)</>');

        $enumPermissions = array_keys(PermissionName::allPermissions());
        $dbPermissions   = Permission::pluck('name')->toArray();

        $missing = array_diff($enumPermissions, $dbPermissions);
        $extra   = array_diff($dbPermissions, $enumPermissions);

        if (empty($missing)) {
            $this->line('   ✓ All ' . count($enumPermissions) . ' permissions exist in the database');
        } else {
            $this->issues++;
            $this->warn('   ✗ ' . count($missing) . ' permissions are MISSING from the database:');
            foreach ($missing as $perm) {
                $this->line("       - {$perm}");
            }
        }

        if ( ! empty($extra)) {
            $this->line('   ℹ  ' . count($extra) . ' extra permission(s) in DB not in enum (OK if custom):');
            foreach ($extra as $perm) {
                $this->line("       ~ {$perm}");
            }
        }

        $this->newLine();
    }

    private function checkRoles(): void
    {
        $this->line('<fg=cyan>② Privileged roles (owner / administrator)</>');

        $requiredRoles = ['owner', 'administrator'];
        foreach ($requiredRoles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $this->line("   ✓ Role '{$roleName}' exists (id={$role->id})");
            } else {
                $this->issues++;
                $this->warn("   ✗ Role '{$roleName}' is MISSING from the database!");
            }
        }

        $this->newLine();
    }

    private function checkRolePermissions(): void
    {
        $this->line('<fg=cyan>③ Role ↔ permission assignments</>');

        $totalPermCount = Permission::count();
        $roles          = Role::whereIn('name', ['owner', 'administrator'])->get();

        if ($roles->isEmpty()) {
            $this->warn('   ✗ No owner/administrator roles found — skipping');

            return;
        }

        foreach ($roles as $role) {
            $rolePermCount = $role->perms()->count();

            if ($rolePermCount === $totalPermCount) {
                $this->line("   ✓ '{$role->name}' has all {$totalPermCount} permissions");
            } else {
                $this->issues++;
                $missing = $totalPermCount - $rolePermCount;
                $this->warn("   ✗ '{$role->name}' is MISSING {$missing} permission(s) ({$rolePermCount}/{$totalPermCount})");

                // Show which ones are missing
                $rolePermNames = $role->perms()->pluck('name')->toArray();
                $allPermNames  = Permission::pluck('name')->toArray();
                $missingPerms  = array_diff($allPermNames, $rolePermNames);
                foreach (array_slice($missingPerms, 0, 5) as $perm) {
                    $this->line("       - {$perm}");
                }
                if (count($missingPerms) > 5) {
                    $this->line('       ... and ' . (count($missingPerms) - 5) . ' more');
                }
            }
        }

        $this->newLine();
    }

    private function checkUserRole(): void
    {
        $this->line('<fg=cyan>④ User → role assignment</>');

        $identifier = $this->option('user');

        if ($identifier) {
            $user = is_numeric($identifier)
                ? User::find($identifier)
                : User::where('email', $identifier)->first();
        } else {
            $user = User::first();
        }

        if ( ! $user) {
            $this->warn('   ✗ No user found');

            return;
        }

        $this->line("   Checking user: {$user->name} <{$user->email}> (id={$user->id})");

        $roles = $user->roles()->pluck('name')->toArray();

        if (empty($roles)) {
            $this->issues++;
            $this->warn('   ✗ User has NO roles assigned!');
        } else {
            $this->line('   ✓ User roles: ' . implode(', ', $roles));
            $hasPrivileged = ! empty(array_intersect($roles, ['owner', 'administrator']));
            if ( ! $hasPrivileged) {
                $this->issues++;
                $this->warn("   ✗ User is not assigned to 'owner' or 'administrator' role!");
            } else {
                // Actually test can()
                $this->line('   Testing can(task-create)...');
                // Bypass cache to check DB truth
                $canCreate = $user->roles()
                    ->whereIn('roles.name', ['owner', 'administrator'])
                    ->whereHas('perms', fn ($q) => $q->where('name', 'task-create'))
                    ->exists();

                if ($canCreate) {
                    $this->line('   ✓ DB confirms user CAN task-create via their role');
                    $this->line('   ℹ  If permission denied in web request, the issue is stale CACHE (not DB).');
                } else {
                    $this->issues++;
                    $this->warn('   ✗ DB confirms user CANNOT task-create — permissions not linked to role');
                }
            }
        }

        $this->newLine();
    }

    private function runFixes(): void
    {
        // 1. Create missing permissions
        $this->line('  Creating missing permissions...');
        $created = 0;
        foreach (PermissionName::allPermissions() as $name => $data) {
            if ( ! Permission::where('name', $name)->exists()) {
                Permission::create([
                    'external_id'  => Str::uuid()->toString(),
                    'display_name' => $data['display_name'],
                    'name'         => $name,
                    'description'  => $data['description'],
                    'grouping'     => $data['grouping'],
                ]);
                $this->line("    + Created permission: {$name}");
                $created++;
            }
        }
        $this->line("  ✓ Permissions created: {$created}");

        // 2. Attach all permissions to owner/administrator roles
        $this->line('  Attaching permissions to privileged roles...');
        $allPermIds = Permission::pluck('id')->toArray();
        $attached   = 0;
        foreach (['owner', 'administrator'] as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ( ! $role) {
                $this->warn("  ✗ Role '{$roleName}' not found — skipping");
                continue;
            }
            $existing = $role->perms()->pluck('id')->toArray();
            $missing  = array_diff($allPermIds, $existing);
            if ( ! empty($missing)) {
                // Use syncWithoutDetaching to prevent duplicate key errors (consistent with UpgradeCommand)
                $role->perms()->syncWithoutDetaching($missing);
                $attached += count($missing);
                $this->line('  + Attached ' . count($missing) . " permissions to '{$roleName}'");
            } else {
                $this->line("  ✓ '{$roleName}' already has all permissions");
            }
        }

        // 3. Flush Entrust cache
        $this->line('  Flushing Entrust cache...');
        EntrustCacheService::clear();
        $this->line('  ✓ Cache flushed');

        $this->newLine();
        $this->info('✅ All fixes applied! Please log out and log back in.');
    }
}
