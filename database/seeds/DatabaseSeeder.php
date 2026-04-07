<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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
        $this->call('IndustriesTableSeeder');
        $this->call('SettingsTableSeeder');
        
        $this->call('PermissionsTableSeeder');
        $this->call('RolesTablesSeeder');
        $this->call('RolePermissionTableSeeder');
        
        $this->call('UsersTableSeeder');
        $this->call('UserRoleTableSeeder');
        $this->call('DepartmentsTableSeeder');
        
        $this->call('InvoiceReductionSeeder');
    }
}
