<?php

namespace Database\Seeders;

use App\Enums\PermissionName;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Use PermissionName enum as the single source of truth
        $permissions = PermissionName::allPermissions();

        // Always ensure external_id is set for every permission, even if the array is changed in the future
        foreach ($permissions as $name => $data) {
            $existing = Permission::where('name', $name)->first();
            if ( ! $existing) {
                Permission::create([
                    'external_id'  => Str::uuid()->toString(),
                    'display_name' => $data['display_name'],
                    'name'         => $name,
                    'description'  => $data['description'],
                    'grouping'     => $data['grouping'],
                ]);
            }
        }
    }
}
