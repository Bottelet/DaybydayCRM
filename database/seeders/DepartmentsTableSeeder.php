<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class DepartmentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Don't hardcode the ID - let auto-increment handle it
        // This prevents duplicate key errors when seeder runs multiple times
        $department = Department::firstOrCreate(
            ['name' => 'Management'],
            [
                'external_id' => Uuid::uuid4(),
                'description' => 'Management Department',
            ]
        );

        // Only insert department_user relationship if both exist and not already associated
        if ($department && DB::table('users')->where('id', 1)->exists()) {
            $exists = DB::table('department_user')
                ->where('department_id', $department->id)
                ->where('user_id', 1)
                ->exists();
            
            if (!$exists) {
                DB::table('department_user')->insert([
                    'department_id' => $department->id,
                    'user_id' => 1,
                ]);
            }
        }
    }
}
