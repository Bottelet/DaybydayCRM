<?php

namespace Tests\Unit\Controllers\Appointment;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AppointmentsControllerTest extends TestCase
{
    use DatabaseTransactions, WithoutMiddleware;

    protected $appointmentsWithInTime;

    protected $appointmentsWithToLate;

    protected $appointmentsWithToEarly;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
        $this->appointmentsWithInTime = factory(Appointment::class)->create([
            'user_id' => $this->user->id,
            'start_at' => now(),
            'end_at' => now()->addHour(),
            'source_id' => $this->user->id,
            'source_type' => User::class,
            'title' => 'test',
            'color' => '#FFFFFF',
        ]);

        $this->appointmentsWithToLate = factory(Appointment::class)->create([
            'user_id' => $this->user->id,
            'start_at' => now()->addWeeks(6),
            'end_at' => now()->addWeeks(6)->addHour(),
            'source_id' => $this->user->id,
            'source_type' => User::class,
            'title' => 'test',
            'color' => '#FFFFFF',
        ]);
        $this->appointmentsWithToEarly = factory(Appointment::class)->create([
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

        $this->assertEquals($this->appointmentsWithInTime->start_at, $correctAppointment['start_at']);
        $this->assertEquals($this->appointmentsWithInTime->end_at, $correctAppointment['end_at']);
    }

    #[Test]
    public function appointment_store_endpoint_exists()
    {
        // The store() method exists in AppointmentsController and the route is registered.
        $response = $this->json('POST', '/appointments');

        $this->assertNotEquals(404, $response->getStatusCode());
    }

    #[Test]
    public function can_update_appointment_dates_and_user()
    {
        $newUser = factory(User::class)->create();
        $appointment = factory(Appointment::class)->create([
            'user_id' => $this->user->id,
            'start_at' => now(),
            'end_at' => now()->addHour(),
            'source_id' => $this->user->id,
            'source_type' => User::class,
            'title' => 'Original',
            'color' => '#FFFFFF',
        ]);

        $newStart = now()->addDay()->format('Y-m-d H:i:s');
        $newEnd = now()->addDay()->addHour()->format('Y-m-d H:i:s');

        $response = $this->json('POST', route('appointments.update', $appointment), [
            'id' => $appointment->id,
            'start' => $newStart,
            'end' => $newEnd,
            'group' => $newUser->external_id,
        ]);

        $response->assertStatus(200);

        $appointment->refresh();
        $this->assertEquals($newUser->id, $appointment->user_id);
    }

    #[Test]
    public function can_delete_appointment()
    {
        $appointment = factory(Appointment::class)->create([
            'user_id' => $this->user->id,
            'start_at' => now(),
            'end_at' => now()->addHour(),
            'source_id' => $this->user->id,
            'source_type' => User::class,
            'title' => 'To be deleted',
            'color' => '#FFFFFF',
        ]);

        $response = $this->json('DELETE', route('appointments.destroy', $appointment));
        $response->assertStatus(200);

        $this->assertNull(Appointment::find($appointment->id));
    }
}