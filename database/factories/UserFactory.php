<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Department;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'external_id' => $this->faker->uuid,
            'email' => $this->faker->unique()->safeEmail,
            'password' => bcrypt('secretpassword'),
            'address' => $this->faker->secondaryAddress(),
            'primary_number' => $this->faker->randomNumber(8),
            'secondary_number' => $this->faker->randomNumber(8),
            'remember_token' => null,
            'language' => 'en',
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (User $user) {
            // Ensure at least one department exists in parallel-safe manner
            $department = Department::first() ?? Department::factory()->create();
            $user->department()->attach($department->id);
        });
    }

    /**
     * Attach a role to the user (parallel-safe).
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function withRole(string $roleName = 'employee')
    {
        return $this->afterCreating(function (User $user) use ($roleName) {
            $roleData = [
                'employee' => ['name' => 'employee', 'display_name' => 'Employee'],
                'owner' => ['name' => 'owner', 'display_name' => 'Owner'],
                'administrator' => ['name' => 'administrator', 'display_name' => 'Administrator'],
                'admin' => ['name' => 'administrator', 'display_name' => 'Administrator'],
                'manager' => ['name' => 'manager', 'display_name' => 'Manager'],
            ];

            $data = $roleData[$roleName] ?? [
                'name' => $roleName,
                'display_name' => ucfirst($roleName),
            ];

            $role = Role::firstOrCreate(
                ['name' => $data['name']],
                [
                    'display_name' => $data['display_name'],
                    'description' => $data['display_name'].' role',
                    'external_id' => Str::uuid()->toString(),
                ]
            );

            $user->attachRole($role);
        });
    }
}
