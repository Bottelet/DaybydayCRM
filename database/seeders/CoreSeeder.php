<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * CoreSeeder.
 *
 * Orchestrates every seeder that must run before any data can be created.
 * Each sub-seeder is idempotent: safe to re-run on an existing database.
 *
 * Previously this logic lived in DatabaseSeeder. Renamed so that DemoTableSeeder
 * and DummyDatabaseSeeder can both call it without circular references.
 */
class CoreSeeder extends Seeder
{
    private array $seeders = [
        'Settings'    => SettingsTableSeeder::class,
        'Industries'  => IndustriesTableSeeder::class,
        'Statuses'    => StatusTableSeeder::class,
        'Permissions' => PermissionsTableSeeder::class,
        'Roles'       => RolesTablesSeeder::class,
        'Role perms'  => RolePermissionTableSeeder::class,
        'Departments' => DepartmentsTableSeeder::class,
        'Admin user'  => UsersTableSeeder::class,
        'User roles'  => UserRoleTableSeeder::class,
    ];

    public function run(): void
    {
        $this->command->info('');
        $this->command->info('  ── Core ──────────────────────────────────');

        $bar = $this->command->getOutput()->createProgressBar(count($this->seeders));
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% — %message%');
        $bar->start();

        foreach ($this->seeders as $label => $class) {
            $bar->setMessage($label);
            $this->call($class);
            $bar->advance();
        }

        $bar->setMessage('Done ✓');
        $bar->finish();
        $this->command->info('');
    }
}
