<?php

namespace Tests\Unit\Offers;

use App\Models\Appointment;
use App\Models\User;
use App\Services\Appointment\AppointmentService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\AbstractTestCase;

class AppointmentServiceTest extends AbstractTestCase
{
    use RefreshDatabase;

    private AppointmentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AppointmentService();
        Carbon::setTestNow('2024-01-15 12:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    #[Test]
    public function it_updates_appointment_time()
    {
        /* Arrange */
        $appointment = Appointment::factory()->create();
        $startTime   = '2024-01-20 09:00:00';
        $endTime     = '2024-01-20 10:00:00';

        /* Act */
        $result = $this->service->updateTime($appointment, $startTime, $endTime);

        /* Assert */
        $this->assertTrue($result);
        $fresh = $appointment->fresh();
        $this->assertEquals('2024-01-20 09:00:00', $fresh->start_at->format('Y-m-d H:i:s'));
        $this->assertEquals('2024-01-20 10:00:00', $fresh->end_at->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function it_updates_appointment_time_and_user()
    {
        /* Arrange */
        $appointment = Appointment::factory()->create();
        $user        = User::factory()->create();
        $startTime   = '2024-01-20 14:00:00';
        $endTime     = '2024-01-20 15:00:00';

        /* Act */
        $result = $this->service->updateAppointmentTime(
            $appointment,
            $startTime,
            $endTime,
            $user->external_id
        );

        /* Assert */
        $this->assertTrue($result);
        $fresh = $appointment->fresh();
        $this->assertEquals($user->id, $fresh->user_id);
        $this->assertEquals('2024-01-20 14:00:00', $fresh->start_at->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function it_reassigns_to_user()
    {
        /* Arrange */
        $appointment = Appointment::factory()->create();
        $newUser     = User::factory()->create();

        /* Act */
        $result = $this->service->reassignToUser($appointment, $newUser);

        /* Assert */
        $this->assertTrue($result);
        $this->assertEquals($newUser->id, $appointment->fresh()->user_id);
    }

    #[Test]
    public function it_reassigns_by_external_id()
    {
        /* Arrange */
        $appointment = Appointment::factory()->create();
        $newUser     = User::factory()->create();

        /* Act */
        $result = $this->service->reassignToUserByExternalId($appointment, $newUser->external_id);

        /* Assert */
        $this->assertTrue($result);
        $this->assertEquals($newUser->id, $appointment->fresh()->user_id);
    }

    #[Test]
    public function it_throws_exception_for_nonexistent_user()
    {
        /* Arrange */
        $appointment = Appointment::factory()->create();

        /* Act & Assert */
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("User with external ID 'nonexistent-id' not found");
        $this->service->reassignToUserByExternalId($appointment, 'nonexistent-id');
    }

    #[Test]
    public function it_deletes_appointment()
    {
        /* Arrange */
        $appointment   = Appointment::factory()->create();
        $appointmentId = $appointment->id;

        /* Act */
        $result = $this->service->deleteAppointment($appointment);

        /* Assert */
        $this->assertTrue($result);
        $this->assertSoftDeleted('appointments', ['id' => $appointmentId]);
    }

    #[Test]
    public function it_gets_appointments_for_user_in_range()
    {
        /* Arrange */
        $user = User::factory()->create();
        Appointment::factory()->create([
            'user_id'  => $user->id,
            'start_at' => Carbon::parse('2024-01-15 09:00'),
            'end_at'   => Carbon::parse('2024-01-15 10:00'),
        ]);
        Appointment::factory()->create([
            'user_id'  => $user->id,
            'start_at' => Carbon::parse('2024-01-20 09:00'),
            'end_at'   => Carbon::parse('2024-01-20 10:00'),
        ]);
        Appointment::factory()->create([
            'user_id'  => $user->id,
            'start_at' => Carbon::parse('2024-02-01 09:00'),
            'end_at'   => Carbon::parse('2024-02-01 10:00'),
        ]);

        /* Act */
        $appointments = $this->service->getAppointmentsForUserInRange(
            $user,
            Carbon::parse('2024-01-15'),
            Carbon::parse('2024-01-20')
        );

        /* Assert */
        $this->assertCount(2, $appointments);
    }

    #[Test]
    public function it_finds_appointment_by_id()
    {
        /* Arrange */
        $appointment = Appointment::factory()->create();

        /* Act */
        $found = $this->service->findById($appointment->id);

        /* Assert */
        $this->assertNotNull($found);
        $this->assertEquals($appointment->id, $found->id);
    }

    #[Test]
    public function it_returns_null_for_nonexistent_appointment()
    {
        /* Act */
        $found = $this->service->findById(99999);

        /* Assert */
        $this->assertNull($found);
    }

    #[Test]
    public function it_checks_user_has_appointment_at_time()
    {
        /* Arrange */
        $user = User::factory()->create();
        Appointment::factory()->create([
            'user_id'  => $user->id,
            'start_at' => Carbon::parse('2024-01-15 09:00'),
            'end_at'   => Carbon::parse('2024-01-15 10:00'),
        ]);

        /* Act & Assert */
        $this->assertTrue($this->service->hasAppointmentAtTime(
            $user,
            Carbon::parse('2024-01-15 09:30')
        ));
        $this->assertFalse($this->service->hasAppointmentAtTime(
            $user,
            Carbon::parse('2024-01-15 11:00')
        ));
    }

    #[Test]
    public function it_parses_different_date_formats()
    {
        /* Arrange */
        $appointment = Appointment::factory()->create();

        /* Act */
        $this->service->updateTime(
            $appointment,
            '2024-01-20',  // Date only
            '2024-01-20 11:00'  // Date with time
        );

        /* Assert */
        $fresh = $appointment->fresh();
        $this->assertEquals('2024-01-20', $fresh->start_at->format('Y-m-d'));
        $this->assertEquals('2024-01-20', $fresh->end_at->format('Y-m-d'));
    }
}
