<?php

namespace Tests\Unit\Models;

use App\Models\Appointment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Tests\AbstractTestCase;

class AppointmentModelBootTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Freeze time for deterministic tests
        Carbon::setTestNow('2024-01-15 12:00:00');

        $this->user = User::factory()->create();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    # region happy_path

    #[Test]
    public function appointment_stores_explicit_external_id_when_provided()
    {
        /** Arrange */
        $externalId = Uuid::uuid4()->toString();

        /** Act */
        $appointment = Appointment::create([
            'external_id' => $externalId,
            'title' => 'Test Appointment',
            'start_at' => Carbon::now(),
            'end_at' => Carbon::now()->addHour(),
            'user_id' => $this->user->id,
            'color' => '#FF0000',
            'source_type' => User::class,
            'source_id' => $this->user->id,
        ]);

        /** Assert */
        $this->assertNotNull($appointment->external_id);
        $this->assertNotEmpty($appointment->external_id);
        $this->assertEquals($externalId, $appointment->external_id);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $appointment->external_id
        );
    }

    #[Test]
    public function appointment_generates_unique_external_ids_for_each_record()
    {
        /** Arrange */
        $appointment1 = Appointment::create([
            'external_id' => Uuid::uuid4()->toString(),
            'title' => 'Appointment One',
            'start_at' => Carbon::now(),
            'end_at' => Carbon::now()->addHour(),
            'user_id' => $this->user->id,
            'color' => '#FF0000',
            'source_type' => User::class,
            'source_id' => $this->user->id,
        ]);

        /** Act */
        $appointment2 = Appointment::create([
            'external_id' => Uuid::uuid4()->toString(),
            'title' => 'Appointment Two',
            'start_at' => Carbon::now()->addDay(),
            'end_at' => Carbon::now()->addDay()->addHour(),
            'user_id' => $this->user->id,
            'color' => '#00FF00',
            'source_type' => User::class,
            'source_id' => $this->user->id,
        ]);

        /** Assert */
        $this->assertNotEquals($appointment1->external_id, $appointment2->external_id);
    }

    #[Test]
    public function appointment_factory_creates_record_with_external_id()
    {
        /** Arrange */
        // User already created in setUp()

        /** Act */
        $appointment = Appointment::factory()->create([
            'user_id' => $this->user->id,
            'source_type' => User::class,
            'source_id' => $this->user->id,
            'color' => '#FFFFFF',
        ]);

        /** Assert */
        $this->assertNotNull($appointment->external_id);
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'external_id' => $appointment->external_id,
        ]);
    }

    # endregion

    # region edge_cases

    #[Test]
    public function appointment_preserves_provided_external_id()
    {
        /** Arrange */
        $customExternalId = 'custom-appointment-uuid-6789';

        /** Act */
        $appointment = Appointment::create([
            'external_id' => $customExternalId,
            'title' => 'Test Appointment',
            'start_at' => Carbon::now(),
            'end_at' => Carbon::now()->addHour(),
            'user_id' => $this->user->id,
            'color' => '#FF0000',
            'source_type' => User::class,
            'source_id' => $this->user->id,
        ]);

        /** Assert */
        $this->assertEquals($customExternalId, $appointment->external_id);
    }

    # endregion
}
