<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;

class Bootstrap
{
    /*
    |--------------------------------------------------------------------------
    | Bootstrap The Test Environment
    |--------------------------------------------------------------------------
    |
    | You may specify console commands that execute once before your test is
    | run. You are free to add your own additional commands or logic into
    | this file as needed in order to help your test suite run quicker.
    |
    */
    use CreatesApplication;

    public static function setUpBeforeClass(): void
    {
        $console = (new self())->createApplication()->make(Kernel::class);
        $commands = [
            'config:cache',
            'event:cache',
            'migrate:fresh', // Ensure all tables exist in the in-memory SQLite DB
        ];
        foreach ($commands as $command) {
            $console->call($command);
        }
    }

    public static function tearDownAfterClass(): void
    {
        array_map('unlink', glob('bootstrap/cache/*.phpunit.php'));
    }
}
