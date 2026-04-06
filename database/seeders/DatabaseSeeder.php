<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

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
