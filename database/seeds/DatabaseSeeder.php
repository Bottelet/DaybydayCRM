<?php

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
        $this->call('StatusTableSeeder');
        $this->call('UsersTableSeeder');
        $this->call('IndustriesTableSeeder');
        $this->call('DepartmentsTableSeeder');
        $this->call('SettingsTableSeeder');
        $this->call('PermissionsTableSeeder');
        $this->call('RolesTablesSeeder');
        $this->call('RolePermissionTableSeeder');
        $this->call('UserRoleTableSeeder');
    }
}
