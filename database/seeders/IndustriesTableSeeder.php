<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class IndustriesTableSeeder extends Seeder
{
    private array $industries = [
        'Accommodations', 'Accounting', 'Auto', 'Beauty & Cosmetics', 'Carpenter',
        'Communications', 'Computer & IT', 'Construction', 'Consulting', 'Education',
        'Electronics', 'Entertainment', 'Food & Beverages', 'Legal Services', 'Marketing',
        'Real Estate', 'Retail', 'Sports', 'Technology', 'Tourism',
        'Transportation', 'Travel', 'Utilities', 'Web Services', 'Other',
    ];

    public function run(): void
    {
        foreach ($this->industries as $name) {
            DB::table('industries')->insertOrIgnore([
                'name'        => $name,
                'external_id' => Uuid::uuid4()->toString(),
            ]);
        }
    }
}
