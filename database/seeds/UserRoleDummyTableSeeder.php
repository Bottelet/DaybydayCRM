<?php

use Illuminate\Database\Seeder;
use App\RoleUser;

class UserRoleDummyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $newrole = new RoleUser;
        $newrole->role_id = '1';
        $newrole->user_id = '2';
        $newrole->save();
        $newrole = new RoleUser;
        $newrole->role_id = '2';
        $newrole->user_id = '3';
        $newrole->save();
        $newrole = new RoleUser;
        $newrole->role_id = '3';
        $newrole->user_id = '4';
        $newrole->save();
        $newrole = new RoleUser;
        $newrole->role_id = '3';
        $newrole->user_id = '5';
        $newrole->save();
        $newrole = new RoleUser;
        $newrole->role_id = '3';
        $newrole->user_id = '6';
        $newrole->save();
    }
}
