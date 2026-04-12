<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Comment;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Task;
use App\Models\User;
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
        Task::factory()->count(50)->create([
            'client_id' => Client::all()->random()->id,
            'user_created_id' => User::all()->random()->id,
            'user_assigned_id' => User::all()->random()->id,
        ])->each(function ($t) {
            if (random_int(1, 5) == 1) {
                $invoice = Invoice::factory()->create([
                    'client_id' => $t->client_id,
                ]);
                InvoiceLine::factory()->count(4)->create([
                    'invoice_id' => $invoice->id,
                ]);

                Comment::factory()->count(3)->create([
                    'source_type' => Task::class,
                    'source_id' => $t->id,
                    'user_id' => User::all()->random()->id,
                ]);
            }

            Comment::factory()->count(3)->create([
                'source_type' => Task::class,
                'source_id' => $t->id,
                'user_id' => User::all()->random()->id,
            ]);
        });
    }
}
