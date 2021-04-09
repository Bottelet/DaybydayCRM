<?php
namespace Tests\Unit\Controllers\Appointment;

use App\Models\Appointment;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AppointmentsControllerTest extends TestCase
{
    use DatabaseTransactions, WithoutMiddleware;

    protected $appointmentsWithInTime;
    protected $appointmentsWithToLate;
    protected $appointmentsWithToEarly;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
        $this->appointmentsWithInTime = factory(Appointment::class)->create([
            'user_id' => $this->user->id,
            'start_at' => now(),
            'end_at' => now()->addHour(),
            'source_id' => $this->user->id,
            'source_type' => User::class,
            'title' => 'test'
        ]);

        $this->appointmentsWithToLate = factory(Appointment::class)->create([
            'user_id' => $this->user->id,
            'start_at' => now()->addWeeks(6),
            'end_at' => now()->addWeeks(6)->addHour(),
            'source_id' => $this->user->id,
            'source_type' => User::class,
            'title' => 'test'
        ]);
        $this->appointmentsWithToEarly = factory(Appointment::class)->create([
            'user_id' => $this->user->id,
            'start_at' => now()->subWeeks(4),
            'end_at' => now()->subWeeks(4)->addHour(),
            'source_id' => $this->user->id,
            'source_type' => User::class,
            'title' => 'test'
        ]);
    }

    /** @test * */
    public function can_get_appointments_within_time_slot()
    {
        $correctAppointment = null;
        $r = $this->json('GET', '/appointments/data');

        foreach ($r->decodeResponseJson() as $appointment) {
            $this->assertNotTrue($appointment["external_id"] == $this->appointmentsWithToLate->external_id);
            $this->assertNotTrue($appointment["external_id"] == $this->appointmentsWithToEarly->external_id);
            if ($appointment["external_id"] == $this->appointmentsWithInTime->external_id) {
                $correctAppointment = $appointment;
            }
        }

        $this->assertEquals($this->appointmentsWithInTime->start_at, $correctAppointment["start_at"]);
        $this->assertEquals($this->appointmentsWithInTime->end_at, $correctAppointment["end_at"]);

        $this->assertCount(3, User::whereExternalId($this->user->external_id)->first()->appointments);
    }
}
