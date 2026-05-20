<?php

namespace App\Console;

use App\Console\Commands\ClearEntrustCacheCommand;
use App\Console\Commands\DiagnosePermissionsCommand;
use App\Console\Commands\UpgradeCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\Test::class,
        UpgradeCommand::class,
        ClearEntrustCacheCommand::class,
        DiagnosePermissionsCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('inspire')
            ->hourly();
    }
}
