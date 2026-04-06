<?php

<<<<<<< Updated upstream:database/seeders/DepartmentsTableSeeder.php
namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
=======
use App\Models\Department;
use Illuminate\Database\Seeder;
>>>>>>> Stashed changes:database/seeds/DepartmentsTableSeeder.php
use Ramsey\Uuid\Uuid;

class DepartmentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $department = new Department;
        $department->id = '1';
        $department->external_id = Uuid::uuid4();
        $department->name = 'Management';
        $department->save();

        DB::table('department_user')->insert([
            'department_id' => 1,
            'user_id' => 1,
        ]);
    }
}
