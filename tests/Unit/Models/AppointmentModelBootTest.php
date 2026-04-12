<?php

namespace Tests\Unit\Models;

use App\Models\Appointment;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Tests\AbstractTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AppointmentModelBootTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    #[Test]
    public function appointment_stores_explicit_external_id_when_provided()
    {
        $externalId = Uuid::uuid4()->toString();

        $appointment = Appointment::create([
            'external_id' => $externalId,
            'title' => 'Test Appointment',
            'start_at' => now(),
            'end_at' => now()->addHour(),
            'user_id' => $this->user->id,
            'color' => '#FF0000',
            'source_type' => User::class,
            'source_id' => $this->user->id,
        ]);

        $this->assertNotNull($appointment->external_id);
        $this->assertNotEmpty($appointment->external_id);
        $this->assertEquals($externalId, $appointment->external_id);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $appointment->external_id
        );
    }

    #[Test]
    public function appointment_preserves_provided_external_id()
    {
        $customExternalId = 'custom-appointment-uuid-6789';

        $appointment = Appointment::create([
            'external_id' => $customExternalId,
            'title' => 'Test Appointment',
            'start_at' => now(),
            'end_at' => now()->addHour(),
            'user_id' => $this->user->id,
            'color' => '#FF0000',
            'source_type' => User::class,
            'source_id' => $this->user->id,
        ]);

        $this->assertEquals($customExternalId, $appointment->external_id);
    }

    #[Test]
    public function appointment_generates_unique_external_ids_for_each_record()
    {
        $appointment1 = Appointment::create([
            'external_id' => Uuid::uuid4()->toString(),
            'title' => 'Appointment One',
            'start_at' => now(),
            'end_at' => now()->addHour(),
            'user_id' => $this->user->id,
            'color' => '#FF0000',
            'source_type' => User::class,
            'source_id' => $this->user->id,
        ]);

        $appointment2 = Appointment::create([
            'external_id' => Uuid::uuid4()->toString(),
            'title' => 'Appointment Two',
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHour(),
            'user_id' => $this->user->id,
            'color' => '#00FF00',
            'source_type' => User::class,
            'source_id' => $this->user->id,
        ]);

        $this->assertNotEquals($appointment1->external_id, $appointment2->external_id);
    }

    #[Test]
    public function appointment_factory_creates_record_with_external_id()
    {
        $appointment = Appointment::factory()->create([
            'user_id' => $this->user->id,
            'source_type' => User::class,
            'source_id' => $this->user->id,
            'color' => '#FFFFFF',
        ]);

        $this->assertNotNull($appointment->external_id);
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'external_id' => $appointment->external_id,
        ]);
    }
}
