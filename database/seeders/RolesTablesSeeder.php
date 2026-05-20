<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;

class RolesTablesSeeder extends Seeder
{
    private array $roles = [
        ['name' => 'owner',         'display_name' => 'Owner',         'description' => 'Full system owner'],
        ['name' => 'administrator', 'display_name' => 'Administrator', 'description' => 'System administrator'],
        ['name' => 'manager',       'display_name' => 'Manager',       'description' => 'Department manager'],
        ['name' => 'employee',      'display_name' => 'Employee',      'description' => 'Regular employee'],
    ];

    public function run(): void
    {
        foreach ($this->roles as $def) {
            Role::query()->firstOrCreate(
                ['name' => $def['name']],
                [
                    'external_id'  => Uuid::uuid4()->toString(),
                    'display_name' => $def['display_name'],
                    'description'  => $def['description'],
                ]
            );
        }
    }
}
