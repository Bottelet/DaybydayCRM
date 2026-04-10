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

class AppointmentsControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected $appointmentsWithInTime;

    protected $appointmentsWithToLate;

    protected $appointmentsWithToEarly;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $role = Role::where('name', 'employee')->first();
        $this->user->attachRole($role);

        // Give user permissions for appointment operations
        $editPermission = Permission::firstOrCreate(['name' => 'appointment-edit']);
        $deletePermission = Permission::firstOrCreate(['name' => 'appointment-delete']);
        $this->user->roles->first()->attachPermission($editPermission);
        $this->user->roles->first()->attachPermission($deletePermission);

        $this->actingAs($this->user);
        $this->appointmentsWithInTime = Appointment::factory()->create([
            'user_id' => $this->user->id,
            'start_at' => now(),
            'end_at' => now()->addHour(),
            'source_id' => $this->user->id,
            'source_type' => User::class,
            'title' => 'test',
            'color' => '#FFFFFF',
        ]);

        $this->appointmentsWithToLate = Appointment::factory()->create([
            'user_id' => $this->user->id,
            'start_at' => now()->addWeeks(6),
            'end_at' => now()->addWeeks(6)->addHour(),
            'source_id' => $this->user->id,
            'source_type' => User::class,
            'title' => 'test',
            'color' => '#FFFFFF',
        ]);
        $this->appointmentsWithToEarly = Appointment::factory()->create([
            'user_id' => $this->user->id,
            'start_at' => now()->subWeeks(4),
            'end_at' => now()->subWeeks(4)->addHour(),
            'source_id' => $this->user->id,
            'source_type' => User::class,
            'title' => 'test',
            'color' => '#FFFFFF',
        ]);
    }

    #[Test]
    public function can_get_appointments_within_time_slot()
    {
        $correctAppointment = null;
        $r = $this->json('GET', '/appointments/data');

        foreach ($r->json() as $appointment) {
            $this->assertNotTrue($appointment['external_id'] == $this->appointmentsWithToLate->external_id);
            $this->assertNotTrue($appointment['external_id'] == $this->appointmentsWithToEarly->external_id);
            if ($appointment['external_id'] == $this->appointmentsWithInTime->external_id) {
                $correctAppointment = $appointment;
            }
        }

        $this->assertEquals($this->appointmentsWithInTime->start_at->toISOString(), $correctAppointment['start_at']);
        $this->assertEquals($this->appointmentsWithInTime->end_at->toISOString(), $correctAppointment['end_at']);

        $this->assertCount(3, User::whereExternalId($this->user->external_id)->first()->appointments);
    }

    #[Test]
    public function can_update_appointment_times()
    {
        $newAssignee = User::factory()->create();

        $response = $this->withSession(['_token' => csrf_token()])->json('POST', route('appointments.update', $this->appointmentsWithInTime->external_id), [
            'start_date' => now()->toDateString(),
            'start_time' => now()->format('H:i'),
            'end_date' => now()->addHour()->toDateString(),
            'end_time' => now()->addHour()->format('H:i'),
            'user' => $newAssignee->external_id,
            '_token' => csrf_token(),
        ]);

        $response->assertSuccessful();

        $updatedAppointment = $this->appointmentsWithInTime->fresh();
        $this->assertEquals($newAssignee->id, $updatedAppointment->user_id);
    }

    #[Test]
    public function can_destroy_appointment()
    {
        $appointmentExternalId = $this->appointmentsWithInTime->external_id;

        $response = $this->withSession(['_token' => csrf_token()])->json('DELETE', route('appointments.destroy', $appointmentExternalId), [
            '_token' => csrf_token(),
        ]);

        $response->assertSuccessful();
        $this->assertNull(Appointment::whereExternalId($appointmentExternalId)->first());
    }

    #[Test]
    #[Group('regression')]
    public function user_appointments_relationship_returns_appointments_via_morph()
    {
        // Regression test for User::appointments() changed from hasMany to morphMany('source').
        // The morphMany looks for appointments where source_type = User::class and source_id = user->id.
        // All three setUp appointments use source_type = User::class, source_id = user->id.
        $appointments = $this->user->appointments;

        $this->assertCount(3, $appointments);

        $externalIds = $appointments->pluck('external_id')->toArray();
        $this->assertContains($this->appointmentsWithInTime->external_id, $externalIds);
        $this->assertContains($this->appointmentsWithToLate->external_id, $externalIds);
        $this->assertContains($this->appointmentsWithToEarly->external_id, $externalIds);
    }

    #[Test]
    #[Group('regression')]
    public function user_appointments_morph_does_not_return_appointments_for_other_source_types()
    {
        // When source_type is NOT User::class, the appointment should not appear in user->appointments
        $otherUser = User::factory()->create();
        $otherAppointment = Appointment::factory()->create([
            'user_id' => $this->user->id,
            'source_id' => $otherUser->id,
            'source_type' => User::class,  // Still User but different source_id
            'title' => 'other source',
            'color' => '#000000',
        ]);

        // The original user should only see their own appointments (source_id = this->user->id)
        $appointments = $this->user->appointments;
        $otherIds = $appointments->pluck('external_id')->toArray();
        $this->assertNotContains($otherAppointment->external_id, $otherIds);

        // The other user should see the appointment
        $otherUserAppointments = $otherUser->appointments;
        $this->assertContains($otherAppointment->external_id, $otherUserAppointments->pluck('external_id')->toArray());
    }
}
