<?php

namespace Database\Seeders\Dummy;

use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProjectsDummyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Project::factory()->count(30)->create([
            'client_id'        => Client::all()->random()->id,
            'user_created_id'  => User::all()->random()->id,
            'user_assigned_id' => User::all()->random()->id,
        ]);
    }
}
