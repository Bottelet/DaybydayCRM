<?php

use App\Models\Task;
use App\Models\User;
use App\Models\Client;
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
        factory(Task::class, 50)->create([
            'client_id' => Client::all()->random()->id,
            'user_created_id' => User::all()->random()->id,
            'user_assigned_id' => User::all()->random()->id,
            ])->each(function ($t) {
            if(rand(1,5) == 1) {
                $invoice = factory(\App\Models\Invoice::class)->create([
                    'client_id' => $t->client_id
                ]);
                factory(\App\Models\InvoiceLine::class, 4)->create([
                    'invoice_id' => $invoice->id,
                ]);

                factory(App\Models\Comment::class, 3)->create([
                    'source_type' => Task::class,
                    'source_id' => $t->id,
                    'user_id' => User::all()->random()->id,
                ]);
            }

            factory(App\Models\Comment::class, 3)->create([
                'source_type' => Task::class,
                'source_id' => $t->id,
                'user_id' => User::all()->random()->id,
            ]);
        });
    }
}
