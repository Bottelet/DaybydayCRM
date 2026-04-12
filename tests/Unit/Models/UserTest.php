<?php

namespace Tests\Unit\Models;

use App\Models\Appointment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

class UserTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Freeze time for deterministic tests
        Carbon::setTestNow('2024-01-15 12:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    # region happy_path

    #[Test]
    public function it_user_has_many_appointments()
    {
        /** Arrange */
        $user = User::factory()->create();

        $appointment = Appointment::factory()->create([
            'source_type' => User::class,
            'source_id' => $user->id,
        ]);

        /** Act */
        $user = $user->fresh();

        /** Assert */
        $this->assertCount(1, $user->appointments);
        $this->assertEquals($appointment->id, $user->appointments->first()->id);
    }

    # endregion
}
