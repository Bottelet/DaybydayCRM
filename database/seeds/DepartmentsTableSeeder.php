<?php

use Illuminate\Database\Seeder;

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
        $createDep = new Department;
        $createDep->id = '1';
        $createDep->name = 'Managment';
        $createDep->save();

        \DB::table('department_user')->insert([
            'department_id' => 1,
            'user_id' => 1
        ]);
    }
}
