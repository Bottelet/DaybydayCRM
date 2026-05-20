<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\WorldBuilder;
use Illuminate\Database\Seeder;

/**
 * DemoSeeder.
 *
 * Rich, human-friendly demo data for showcasing the product.
 * The relational graph logic (createData) now lives in the WorldBuilder trait
 * so DummyDatabaseSeeder can reuse it without duplication.
 *
 * Run with:  php artisan db:seed --class=DemoTableSeeder
 *
 * Volumes (approximate):
 *   17 users · ~60 clients · 3 projects + 8 tasks + 5 leads per client
 */
class DemoSeeder extends Seeder
{
    use WorldBuilder;

    public function run(): void
    {
        $this->command->info('');
        $this->command->info('  ── Demo data ─────────────────────────────');

        // Core must exist first
        $this->call(CoreSeeder::class);

        $steps = ['Products', 'Users', 'Clients & relations'];
        $bar   = $this->command->getOutput()->createProgressBar(count($steps));
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% — %message%');
        $bar->start();

        // 1. Products
        $bar->setMessage('Products');
        $this->call(ProductSeeder::class);
        $bar->advance();

        // 2. Users – named demo account + bulk factory users per role
        $bar->setMessage('Users');
        $users = $this->createUsers(
            perRole: [
                'administrator' => 2,
                'manager'       => 4,
                'employee'      => 10,
            ],
            namedUsers: [
                [
                    'name'     => 'DaybydayCRM',
                    'email'    => 'demo@daybydaycrm.com',
                    'password' => 'Daybydaycrm123',
                    'role'     => 'owner',
                ],
            ],
        );
        $bar->advance();

        // 3. Full relational tree per user/client
        $bar->setMessage('Clients & relations');
        $this->createClientTree(
            users:             $users,
            clientsPerUser:    rand(3, 5),
            projectsPerClient: 3,
            tasksPerClient:    8,
            leadsPerClient:    5,
            commentsPerItem:   3,
            sparse:            false,
        );
        $bar->advance();

        $bar->setMessage('Done ✓');
        $bar->finish();
        $this->command->info('');
        $this->command->info('');
        $this->command->info('Demo credentials:');
        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                ['Owner',       'admin@admin.com',       'admin123'],
                ['Owner (demo)', 'demo@daybydaycrm.com',  'Daybydaycrm123'],
            ]
        );
    }
}
