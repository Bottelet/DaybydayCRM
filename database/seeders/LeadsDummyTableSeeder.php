<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Seeder;

class LeadsDummyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(Lead::class, 20)->create()->each(function ($l) {
            if (random_int(0, 5) == 1) {
                factory(Comment::class, 3)->create([
                    'source_type' => Lead::class,
                    'source_id' => $l->id,
                    'user_id' => User::all()->random()->id,
                ]);
            }
            factory(Comment::class, 2)->create([
                'source_type' => Lead::class,
                'source_id' => $l->id,
                'user_id' => User::all()->random()->id,
            ]);
        });
    }
}
