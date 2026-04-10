<?php

namespace Tests\Unit\Controllers\Appointment;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Appointment;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

#[Group('security')]
#[Group('appointment-controller')]
class AppointmentSecurityAbstractTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected $appointment;

    protected $unauthorizedUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create and authenticate a user with default role
        $this->user = User::factory()->create();
        $role = Role::where('name', 'employee')->first();
        $this->user->attachRole($role);
        $this->actingAs($this->user);

        $this->appointment = Appointment::factory()->create([
            'user_id' => $this->user->id,
            'start_at' => now(),
            'end_at' => now()->addHour(),
        ]);

        // Create a user without appointment-update permission
        $this->unauthorizedUser = User::factory()->create();
        $this->unauthorizedUser->attachRole($role);

        // Disable CSRF middleware for all tests
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    #[Test]
    public function authorized_user_can_update_appointment()
    {
        // Give user permission to update appointments
        $permission = Permission::firstOrCreate(['name' => 'appointment-edit']);
        $this->user->roles->first()->attachPermission($permission);

        // Use withSession to provide CSRF token
        $response = $this->withSession(['_token' => csrf_token()])->json('POST', route('appointments.update', $this->appointment->external_id), [
            'start' => now()->addDay()->toISOString(),
            'end' => now()->addDay()->addHour()->toISOString(),
            'group' => $this->user->external_id,
            '_token' => csrf_token(),
        ]);

        $response->assertStatus(200);
    }

    #[Test]
    public function unauthorized_user_cannot_update_appointment()
    {
        $this->actingAs($this->unauthorizedUser);

        $response = $this->json('POST', route('appointments.update', $this->appointment->external_id), [
            'start' => now()->addDay()->toISOString(),
            'end' => now()->addDay()->addHour()->toISOString(),
            'group' => $this->user->external_id,
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function appointment_update_requires_permission_check()
    {
        // Remove all permissions from user
        $this->user->roles()->detach();
        $basicRole = Role::where('name', 'employee')->first();
        $this->user->attachRole($basicRole);

        // Use withSession to provide CSRF token
        $response = $this->withSession(['_token' => csrf_token()])->json('POST', route('appointments.update', $this->appointment->external_id), [
            'start' => now()->addDay()->toISOString(),
            'end' => now()->addDay()->addHour()->toISOString(),
            'group' => $this->user->external_id,
            '_token' => csrf_token(),
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function authorized_user_can_delete_appointment()
    {
        // Give user permission to delete appointments
        $permission = Permission::firstOrCreate(['name' => 'appointment-delete']);
        $this->user->roles->first()->attachPermission($permission);

        // Use withSession to provide CSRF token
        $response = $this->withSession(['_token' => csrf_token()])->json('DELETE', route('appointments.destroy', $this->appointment->external_id), [
            '_token' => csrf_token(),
        ]);

        $response->assertStatus(200);
        $this->assertSoftDeleted('appointments', ['id' => $this->appointment->id]);
    }

    #[Test]
    public function unauthorized_user_cannot_delete_appointment()
    {
        $this->actingAs($this->unauthorizedUser);

        // Use withSession to provide CSRF token
        $response = $this->withSession(['_token' => csrf_token()])->json('DELETE', route('appointments.destroy', $this->appointment->external_id), [
            '_token' => csrf_token(),
        ]);

        $response->assertStatus(403);
    }
}
