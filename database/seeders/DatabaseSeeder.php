<?php

namespace Database\Seeders;

use DepartmentsTableSeeder;
use Illuminate\Database\Seeder;
use IndustriesTableSeeder;
use RolePermissionTableSeeder;
use RolesTablesSeeder;
use SettingsTableSeeder;
use StatusTableSeeder;
use UserRoleTableSeeder;
use UsersTableSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(StatusTableSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(IndustriesTableSeeder::class);
        $this->call(DepartmentsTableSeeder::class);
        $this->call(SettingsTableSeeder::class);
        $this->call(PermissionsTableSeeder::class);
        $this->call(RolesTablesSeeder::class);
        $this->call(RolePermissionTableSeeder::class);
        $this->call(UserRoleTableSeeder::class);
    }
}
