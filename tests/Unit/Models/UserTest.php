<?php

namespace Tests\Unit\Models;

use App\Models\Appointment;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends AbstractTestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_has_many_appointments()
    {
        $user = User::factory()->create();

        // Create an appointment for this user
        $appointment = Appointment::factory()->create([
            'source_type' => User::class,
            'source_id' => $user->id,
        ]);

        // Refresh user and check appointments relationship
        $user = $user->fresh();
        $this->assertCount(1, $user->appointments);
        $this->assertEquals($appointment->id, $user->appointments->first()->id);
    }
}
