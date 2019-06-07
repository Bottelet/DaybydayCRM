<?php

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Department::create(['name' => 'Management']);
        Department::create(['name' => 'Inside Sales']);
        Department::create(['name' => 'Outside Sales']);

        \DB::table('department_user')->insert([
            'department_id' => 1,
            'user_id'       => 1,
        ]);
    }
}
