<?php

namespace Database\Seeders;

use App\Models\RoleUser;
use Illuminate\Database\Seeder;

class UserRoleTableSeeder extends Seeder
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
        $newrole->user_id = '1';
        $newrole->timestamps = false;
        $newrole->save();
    }
}
