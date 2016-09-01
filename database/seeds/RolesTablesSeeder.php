<?php

use Illuminate\Database\Seeder;
use App\Models\Role;

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
        $adminRole->display_name = 'Administrator';
        $adminRole->name = 'administrator';
        $adminRole->description = 'System Administrator';
        $adminRole->save();

        $editorRole = new Role;
        $editorRole->display_name = 'Manager';
        $editorRole->name = 'manager';
        $editorRole->description = 'System Manager';
        $editorRole->save();

        $employeeRole = new Role;
        $employeeRole->display_name = 'Employee';
        $employeeRole->name = 'employee';
        $employeeRole->description = 'Employee';
        $employeeRole->save();
    }
}
