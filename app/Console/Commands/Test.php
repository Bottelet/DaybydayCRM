<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Test
 * @package App\Console\Commands
 */
class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run all tests';

    private function runPhpunit($files = array())
    {
        // If no files were specified we run all tests.
        if (empty($files)) {
            $files[] = '';
        }

        $this->info('Running phpunit:');
        $cmd =  base_path() . '/vendor/bin/phpunit -c ' . base_path() . '/phpunit.xml';
        passthru($cmd);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->runPhpunit();
    }

    protected function getOptions()
    {
        return [
            ['phpunit', false, InputOption::VALUE_NONE, 'Run phpunit tests.', null]
        ];
    }

    protected function getArguments()
    {
        return array(
            array('file', InputOption::VALUE_OPTIONAL, 'Specify a test files.')
        );
    }
}
