<?php

use Illuminate\Database\Seeder;

use App\Department;

class DepartmentsDummyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $createDep = new Department;
        $createDep->id = '2';
        $createDep->name = 'Nerds';
        $createDep->save();
        $createDep = new Department;
        $createDep->id = '3';
        $createDep->name = 'Genius';
        $createDep->save();

        \DB::table('department_user')->insert([
            'department_id' => 1,
            'user_id' => 2,
            'department_id' => 2,
            'user_id' => 3,
            'department_id' => 3,
            'user_id' => 4,
            'department_id' => 3,
            'user_id' => 5,
            'department_id' => 2,
            'user_id' => 6
        ]);
    }
}
