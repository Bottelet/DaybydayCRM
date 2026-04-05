<?php

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
            if(rand(0, 5) == 1) {
                factory(App\Models\Comment::class, 3)->create([
                    'source_type' => Lead::class,
                    'source_id' => $l->id,
                    'user_id' => User::all()->random()->id,
                ]);
            }
            factory(App\Models\Comment::class, 2)->create([
                'source_type' => Lead::class,
                'source_id' => $l->id,
                'user_id' => User::all()->random()->id,
            ]);
        });
    }
}
