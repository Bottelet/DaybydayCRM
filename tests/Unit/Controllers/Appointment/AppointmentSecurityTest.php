<?php

namespace Tests\Unit\Controllers\Appointment;

use App\Models\Appointment;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[Group('security')]
#[Group('appointment-controller')]
class AppointmentSecurityTest extends TestCase
{
    use DatabaseTransactions;

    protected $appointment;

    protected $unauthorizedUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->appointment = factory(Appointment::class)->create([
            'user_id' => $this->user->id,
            'start_at' => now(),
            'end_at' => now()->addHour(),
        ]);

        // Create a user without appointment-update permission
        $this->unauthorizedUser = factory(User::class)->create();
        $role = Role::where('name', 'employee')->first();
        $this->unauthorizedUser->attachRole($role);
    }

    #[Test]
    public function authorized_user_can_update_appointment()
    {
        // Give user permission to update appointments
        $permission = Permission::firstOrCreate(['name' => 'appointment-update']);
        $this->user->roles->first()->attachPermission($permission);

        $response = $this->json('POST', route('appointments.update', $this->appointment->external_id), [
            'start' => now()->addDay()->toISOString(),
            'end' => now()->addDay()->addHour()->toISOString(),
            'group' => $this->user->external_id,
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

        $response = $this->json('POST', route('appointments.update', $this->appointment->external_id), [
            'start' => now()->addDay()->toISOString(),
            'end' => now()->addDay()->addHour()->toISOString(),
            'group' => $this->user->external_id,
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function authorized_user_can_delete_appointment()
    {
        // Give user permission to delete appointments
        $permission = Permission::firstOrCreate(['name' => 'appointment-delete']);
        $this->user->roles->first()->attachPermission($permission);

        $response = $this->json('DELETE', route('appointments.destroy', $this->appointment->external_id));

        $response->assertStatus(200);
        $this->assertSoftDeleted('appointments', ['id' => $this->appointment->id]);
    }

    #[Test]
    public function unauthorized_user_cannot_delete_appointment()
    {
        $this->actingAs($this->unauthorizedUser);

        $response = $this->json('DELETE', route('appointments.destroy', $this->appointment->external_id));

        $response->assertStatus(403);
    }
}
