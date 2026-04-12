<?php

namespace Tests\Unit\Controllers\Appointment;

use App\Models\Appointment;
use App\Models\Role;
use App\Models\User;
use App\Enums\PermissionName;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AppointmentsControllerTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected $appointmentsWithInTime;

    protected $appointmentsWithToLate;

    protected $appointmentsWithToEarly;

    protected function setUp(): void
    {
        parent::setUp();

        // Freeze time for deterministic tests
        Carbon::setTestNow('2024-01-15 12:00:00');

        $this->user = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'employee'], ['display_name' => 'Employee']);
        $this->user->attachRole($role);

        // Give user permissions for appointment operations
        $this->withPermissions([
            PermissionName::APPOINTMENT_EDIT,
            PermissionName::APPOINTMENT_DELETE,
        ]);

        $this->appointmentsWithInTime = Appointment::factory()->create([
            'user_id' => $this->user->id,
            'start_at' => Carbon::now(),
            'end_at' => Carbon::now()->addHour(),
            'source_id' => $this->user->id,
            'source_type' => User::class,
            'title' => 'test',
            'color' => '#FFFFFF',
        ]);

        $this->appointmentsWithToLate = Appointment::factory()->create([
            'user_id' => $this->user->id,
            'start_at' => Carbon::now()->addWeeks(6),
            'end_at' => Carbon::now()->addWeeks(6)->addHour(),
            'source_id' => $this->user->id,
            'source_type' => User::class,
            'title' => 'test',
            'color' => '#FFFFFF',
        ]);
        $this->appointmentsWithToEarly = Appointment::factory()->create([
            'user_id' => $this->user->id,
            'start_at' => Carbon::now()->subWeeks(4),
            'end_at' => Carbon::now()->subWeeks(4)->addHour(),
            'source_id' => $this->user->id,
            'source_type' => User::class,
            'title' => 'test',
            'color' => '#FFFFFF',
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    # region happy_path

    #[Test]
    public function can_get_appointments_within_time_slot()
    {
        /** Arrange */
        $correctAppointment = null;

        /** Act */
        $r = $this->json('GET', '/appointments/data');

        /** Assert */
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

    # endregion

    # region crud

    #[Test]
    public function can_update_appointment_times()
    {
        /** Arrange */
        $this->withPermissions(PermissionName::APPOINTMENT_EDIT);
        $appointment = Appointment::factory()->create([
            'user_id' => $this->user->id,
            'start_at' => Carbon::now(),
            'end_at' => Carbon::now()->addHour(),
            'source_id' => $this->user->id,
            'source_type' => User::class,
            'title' => 'test',
            'color' => '#FFFFFF',
        ]);
        $newAssignee = User::factory()->create();

        /** Act */
        $response = $this->withSession(['_token' => csrf_token()])->json('POST', route('appointments.update', $appointment->external_id), [
            'id' => $appointment->id,
            'start' => Carbon::now()->addDay()->toISOString(),
            'end' => Carbon::now()->addDay()->addHour()->toISOString(),
            'group' => $newAssignee->external_id,
            '_token' => csrf_token(),
        ]);

        /** Assert */
        $response->assertSuccessful();
        $updatedAppointment = $appointment->fresh();
        $this->assertEquals($newAssignee->id, $updatedAppointment->user_id);
    }

    #[Test]
    public function can_destroy_appointment()
    {
        /** Arrange */
        $this->withPermissions(PermissionName::APPOINTMENT_DELETE);
        $appointment = Appointment::factory()->create([
            'user_id' => $this->user->id,
            'start_at' => Carbon::now(),
            'end_at' => Carbon::now()->addHour(),
            'source_id' => $this->user->id,
            'source_type' => User::class,
            'title' => 'test',
            'color' => '#FFFFFF',
        ]);
        $appointmentExternalId = $appointment->external_id;

        /** Act */
        $response = $this->withSession(['_token' => csrf_token()])->json('DELETE', route('appointments.destroy', $appointmentExternalId), [
            '_token' => csrf_token(),
        ]);

        /** Assert */
        $response->assertSuccessful();
        $this->assertNull(Appointment::whereExternalId($appointmentExternalId)->first());
    }

    # endregion

    # region edge_cases

    #[Test]
    #[Group('regression')]
    public function user_appointments_relationship_returns_appointments_via_morph()
    {
        /** Arrange */
        // All three setUp appointments use source_type = User::class, source_id = user->id

        /** Act */
        $appointments = $this->user->appointments;

        /** Assert */
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
        /** Arrange */
        $otherUser = User::factory()->create();
        $otherAppointment = Appointment::factory()->create([
            'user_id' => $this->user->id,
            'source_id' => $otherUser->id,
            'source_type' => User::class,
            'title' => 'other source',
            'color' => '#000000',
        ]);

        /** Act */
        $appointments = $this->user->appointments;
        $otherUserAppointments = $otherUser->appointments;

        /** Assert */
        $otherIds = $appointments->pluck('external_id')->toArray();
        $this->assertNotContains($otherAppointment->external_id, $otherIds);
        $this->assertContains($otherAppointment->external_id, $otherUserAppointments->pluck('external_id')->toArray());
    }

    # endregion
}
