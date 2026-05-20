<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
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
        $ownerRole = Role::where('name', 'owner')->first();
        $adminUser = User::orderBy('id')->first();

        if ( ! $ownerRole || ! $adminUser) {
            $this->command->warn('UserRoleTableSeeder: owner role or first user not found, skipping.');

            return;
        }

        // Use syncWithoutDetaching so re-seeding doesn't create duplicates
        $adminUser->roles()->syncWithoutDetaching([$ownerRole->id]);
    }
}
