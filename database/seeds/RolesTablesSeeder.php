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
        $adminRole->description = 'Administrators have superuser access';
        $adminRole->save();

        $editorRole = new Role;
        $editorRole->display_name = 'Manager';
        $editorRole->name = 'manager';
        $editorRole->description = 'Managers have create, update and delete access';
        $editorRole->save();

        $salesrepRole = new Role;
        $salesrepRole->display_name = 'Sales Representative';
        $salesrepRole->name = 'salesrep';
        $salesrepRole->description = 'Sales Representatives have create and update access';
        $salesrepRole->save();

        $employeeRole = new Role;
        $employeeRole->display_name = 'Employee';
        $employeeRole->name = 'employee';
        $employeeRole->description = 'Employees had read-only access';
        $employeeRole->save();
    }
}
