<?php

namespace Database\Seeders;

use App\Models\Lead;
use App\Models\Offer;
use Illuminate\Database\Seeder;

class OfferSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create offers related to leads
        Lead::all()->each(function ($lead) {
            Offer::factory()->count(random_int(0, 2))->create([
                'client_id'   => $lead->client_id,
                'source_id'   => $lead->id,
                'source_type' => Lead::class,
            ]);
        });
    }
}
