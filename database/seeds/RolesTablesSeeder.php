<?php

use Illuminate\Database\Seeder;
use App\Role;

class RolesTablesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
        $adminRole = new Role;
        $adminRole->name = 'Administrator';
        $adminRole->slug = 'administrator';
        $adminRole->description = 'System Administrator';
        $adminRole->save();

        $editorRole = new Role;
        $editorRole->name = 'Manager';
        $editorRole->slug = 'Manager';
        $editorRole->description = 'System Manager';
        $editorRole->save();

        $employeeRole = new Role;
        $employeeRole->name = 'Employee';
        $employeeRole->slug = 'Employee';
        $employeeRole->description = 'Employee';
        $employeeRole->save();
    }
}
