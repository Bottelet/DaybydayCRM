<?php

use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;
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
        $createDep = new Department;
        $createDep->id = '2';
        $createDep->name = 'Nerds';
        $createDep->external_id = Uuid::uuid4();
        $createDep->external_id = Uuid::uuid4();
        $createDep->save();
        $createDep = new Department;
        $createDep->id = '3';
        $createDep->name = 'Genius';
        $createDep->external_id = Uuid::uuid4();
        $createDep->external_id = Uuid::uuid4();
        $createDep->save();

        factory(App\Models\User::class, 5)->create()->each(function ($c) {
        });
    }
}
