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
        $this->call('UsersTableSeeder');
        $this->call('IndustriesTableSeeder');
        //$this->call('ClientsTableSeeder');
        //$this->call('TasksTableSeeder');
        //$this->call('LeadsTableSeeder');
        $this->call('DepartmentsTableSeeder');
        $this->call('SettingsTableSeeder');
        $this->call(RoleUser::class);
        $this->call(seed_roles_table::class);
        $this->call(seed_permissions_table::class);
        $this->call('UserRole');
        $this->call('NotificationCategoriesTableSeeder');
    }
}
