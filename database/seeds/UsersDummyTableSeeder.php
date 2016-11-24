<?php

use Illuminate\Database\Seeder;

use App\Models\Department;
use App\Models\RoleUser;

class UsersDummyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Models\User::class, 5)->create()->each(function ($c) {
        });

        $createDep = new Department;
        $createDep->id = '2';
        $createDep->name = 'Nerds';
        $createDep->save();
        $createDep = new Department;
        $createDep->id = '3';
        $createDep->name = 'Genius';
        $createDep->save();



        $newrole = new RoleUser;
        $newrole->role_id = '1';
        $newrole->user_id = '2';
        $newrole->timestamps = false;
        $newrole->save();
        $newrole = new RoleUser;
        $newrole->role_id = '2';
        $newrole->user_id = '3';
        $newrole->timestamps = false;
        $newrole->save();
        $newrole = new RoleUser;
        $newrole->role_id = '3';
        $newrole->user_id = '4';
        $newrole->timestamps = false;
        $newrole->save();
        $newrole = new RoleUser;
        $newrole->role_id = '3';
        $newrole->user_id = '5';
        $newrole->timestamps = false;
        $newrole->save();
        $newrole = new RoleUser;
        $newrole->role_id = '3';
        $newrole->user_id = '6';
        $newrole->timestamps = false;
        $newrole->save();

        \DB::table('department_user')->insert([
            ['department_id' => 1,
            'user_id' => 2],
            ['department_id' => 2,
            'user_id' => 3],
            ['department_id' => 3,
            'user_id' => 4],
            ['department_id' => 3,
            'user_id' => 5],
            ['department_id' => 2,
            'user_id' => 6]
        ]);
    }
}
