<?php

use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;
use App\Models\Department;

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

        \DB::table('department_user')->insert([
            'department_id' => 1,
            'user_id' => 1
        ]);
    }
}
