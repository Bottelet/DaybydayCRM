<?php

/** @var Factory $factory */

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;

$factory->define(User::class, static function (Faker $faker) {
    return [
        'name' => $faker->name,
        'external_id' => $faker->uuid,
        'email' => $faker->email,
        'password' => bcrypt('secretpassword'),
        'address' => $faker->secondaryAddress(),
        'primary_number' => $faker->randomNumber(8),
        'secondary_number' => $faker->randomNumber(8),
        'remember_token' => null,
        'language' => 'en',
    ];
});

$factory->afterCreating(User::class, static function ($user, $faker) {
    // Ensure at least one department exists
    if (Department::count() === 0) {
        factory(Department::class)->create();
    }
    $user->department()->attach(Department::first()->id);
});

// Factory state: User with a specific role attached
// Usage: factory(User::class)->state('withRole', ['role' => 'employee'])->create()
$factory->state(User::class, 'withRole', function () {
    return [];
})->afterCreatingState(User::class, 'withRole', function ($user, $faker, $attributes) {
    $roleName = $attributes['role'] ?? 'employee';
    
    // Map common role name variations
    $roleNames = [
        'employee' => ['name' => 'employee', 'display_name' => 'Employee'],
        'owner' => ['name' => 'owner', 'display_name' => 'Owner'],
        'administrator' => ['name' => 'administrator', 'display_name' => 'Administrator'],
        'admin' => ['name' => 'administrator', 'display_name' => 'Administrator'],
        'manager' => ['name' => 'manager', 'display_name' => 'Manager'],
    ];
    
    $roleData = $roleNames[$roleName] ?? ['name' => $roleName, 'display_name' => ucfirst($roleName)];
    
    $role = Role::firstOrCreate(
        ['name' => $roleData['name']],
        [
            'display_name' => $roleData['display_name'],
            'description' => $roleData['display_name'] . ' role',
            'external_id' => Str::uuid()->toString(),
        ]
    );
    
    $user->attachRole($role);
});
