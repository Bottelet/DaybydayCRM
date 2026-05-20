<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\WorldBuilder;
use Illuminate\Database\Seeder;

/**
 * TestSeeder.
 *
 * Produces a LEAN, DETERMINISTIC dataset for automated tests (Playwright, PHPUnit).
 * Run with:  php artisan db:seed --class=TestSeeder
 *
 * Design goals:
 *   - Fast  (< 10 s on a local machine)
 *   - Stable email/password credentials that tests can hard-code
 *   - Enough relational data to exercise every major feature
 *   - No randomness that would make assertions flaky
 *
 * Volumes (fixed):
 *   - 1 owner, 1 admin, 2 managers, 3 employees  =  7 users
 *   - 5 products
 *   - 2 clients per user  →  14 clients
 *   - 2 projects / 4 tasks / 3 leads per client   (sparse invoices / appointments)
 */
class TestSeeder extends Seeder
{
    use WorldBuilder;

    // ---------------------------------------------------------------------------
    // Well-known credentials – reference these from your Playwright fixtures
    // ---------------------------------------------------------------------------

    public const USERS = [
        ['name' => 'Test Owner',   'email' => 'owner@test.local',   'password' => 'password', 'role' => 'owner'],
        ['name' => 'Test Admin',   'email' => 'admin@test.local',   'password' => 'password', 'role' => 'administrator'],
        ['name' => 'Test Manager', 'email' => 'manager@test.local', 'password' => 'password', 'role' => 'manager'],
        ['name' => 'Test Employee', 'email' => 'employee@test.local', 'password' => 'password', 'role' => 'employee'],
    ];

    public function run(): void
    {
        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════════');
        $this->command->info('  Test Seeder  (Playwright / CI)');
        $this->command->info('═══════════════════════════════════════════════');

        $this->call(CoreSeeder::class);

        $steps = 4;
        $bar   = $this->command->getOutput()->createProgressBar($steps);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% — %message%');
        $bar->start();

        // 1. Products (fixed set so IDs are predictable)
        $bar->setMessage('Products');
        $this->createProducts(5);
        $bar->advance();

        // 2. Named users with stable credentials
        $bar->setMessage('Users');
        $users = $this->createUsers(
            perRole: [
                'manager'  => 2,
                'employee' => 3,
            ],
            namedUsers: self::USERS,
        );
        $bar->advance();

        // 3. Slim relational tree — enough for UI assertions, fast to build
        $bar->setMessage('Clients, Projects, Tasks, Leads…');
        $this->createClients(
            users:             $users,
            clientsPerUser:    2,
            tasksPerClient:    4,
            leadsPerClient:    3,
            projectsPerClient: 2,
            commentsPerItem:   2,
            sparse:            true,
        );
        $bar->advance();

        // 4. Done
        $bar->setMessage('Done ✓');
        $bar->finish();

        $this->command->info('');
        $this->command->info('');
        $this->command->info('Test credentials (all passwords: "password"):');
        $this->command->table(
            ['Role', 'Email'],
            array_map(fn ($u) => [$u['role'], $u['email']], self::USERS)
        );
    }
}
