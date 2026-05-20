<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentsTableSeeder extends Seeder
{
    private array $departments = ['Management', 'Sales', 'Engineering', 'Support', 'Finance'];

    public function run(): void
    {
        foreach ($this->departments as $name) {
            Department::query()->firstOrCreate(['name' => $name]);
        }

        // Attach admin user (id 1) to Management if both exist and not already linked
        $management = Department::query()->where('name', 'Management')->first();

        if ($management && DB::table('users')->where('id', 1)->exists()) {
            DB::table('department_user')->insertOrIgnore([
                'department_id' => $management->id,
                'user_id'       => 1,
            ]);
        }
    }
}
