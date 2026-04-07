<?php

namespace Tests\Unit\Controllers\Absence;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AbsenceControllerTest extends TestCase
{
    use DatabaseTransactions, WithoutMiddleware;

    #[Test]
    public function can_create_absence_for_other_user()
    {
        $user = factory(User::class)->create();
        $response = $this->json('POST', route('absence.store'), [
            'reason' => 'Sick',
            'user_external_id' => $user->external_id,
            'start_at' => '2020-01-01 08:00:00',
            'end_at' => '2020-01-02 08:00:00',
            'medical_certificate' => null,
            'comment' => 'Sick kid',
        ]);

        $absences = $user->absences;
        $this->assertNotNull(\Session::all()['flash_message']);
        $this->assertCount(1, $absences);
    }

    #[Test]
    public function creating_absence_for_other_users_without_permission_creates_for_user_it_self()
    {
        $actingUser = factory(User::class)->create();
        $this->actingAs($actingUser);

        $absentUser = factory(User::class)->create();
        $response = $this->json('POST', route('absence.store'), [
            'reason' => 'Sick',
            'user_external_id' => $absentUser->external_id,
            'start_at' => '2020-01-01 08:00:00',
            'end_at' => '2020-01-02 08:00:00',
            'medical_certificate' => null,
            'comment' => 'Sick kid',
        ]);

        $this->assertCount(0, $absentUser->absences);
        $this->assertCount(1, $actingUser->absences);
    }

    #[Test]
    public function not_providing_user_external_id_creates_absence_for_authenticated_user()
    {
        $response = $this->json('POST', route('absence.store'), [
            'reason' => 'Sick',
            'start_at' => '2020-01-01 08:00:00',
            'end_at' => '2020-01-02 08:00:00',
            'medical_certificate' => null,
            'comment' => 'Sick kid',
        ]);

        $this->assertNotNull(\Session::all()['flash_message']);
        $this->assertCount(1, auth()->user()->absences);
    }
}
