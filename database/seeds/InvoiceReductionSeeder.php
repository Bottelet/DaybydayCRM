<?php

use Illuminate\Database\Seeder;

class InvoiceReductionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('invoice_reduction')->insert([
            [
                'id' => 1,
                'reduction' => 10.00,
                'created_at' => null,
                'updated_at' => now()
            ],
        ]);
    }
}
