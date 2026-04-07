<?php

use Illuminate\Database\Seeder;
use App\Models\Role;
use Ramsey\Uuid\Uuid;

class RolesTablesSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            ['id' => 1, 'name' => 'owner', 'display_name' => 'Owner'],
            ['id' => 2, 'name' => 'administrator', 'display_name' => 'Administrator'],
            ['id' => 3, 'name' => 'manager', 'display_name' => 'Manager'],
            ['id' => 4, 'name' => 'employee', 'display_name' => 'Employee']
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['name' => $role['name']],
                [
                    'external_id' => Uuid::uuid4(),
                    'display_name' => $role['display_name'],
                    'description' => $role['display_name'],
                    'id' => $role['id']
                ]
            );
        }
    }
}
