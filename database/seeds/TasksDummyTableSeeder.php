<?php

use Illuminate\Database\Seeder;

class TasksDummyTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        factory(App\Models\Task::class, 50)->create()->each(function ($c) {
        });
    }
}
