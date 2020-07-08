<?php
namespace Tests\Unit\Controllers\Absence;

use App\Models\Absence;
use App\Models\Contact;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Client;
use App\Models\User;
use App\Models\Industry;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AbsenceControllerTest extends TestCase
{
    use DatabaseTransactions, WithoutMiddleware;

    /** @test **/
    public function canCreateAbsenceForOtherUser()
    {
        $user = factory(User::class)->create();
        $response = $this->json('POST', route('absence.store'), [
            'reason' => 'Sick',
            'user_external_id' => $user->external_id,
            'start_at' => "2020-01-01 08:00:00",
            'end_at' => "2020-01-02 08:00:00",
            'medical_certificate' => null,
            'comment' => 'Sick kid'
        ]);

        $absences = $user->absences;
        $this->assertNotNull(\Session::all()["flash_message"]);
        $this->assertCount(1, $absences);
    }

    /** @test **/
    public function creatingAbsenceForOtherUsersWithoutPermissionCreatesForUserItSelf()
    {
        $actingUser = factory(User::class)->create();
        $this->actingAs($actingUser);

        $absentUser = factory(User::class)->create();
        $response = $this->json('POST', route('absence.store'), [
            'reason' => 'Sick',
            'user_external_id' => $absentUser->external_id,
            'start_at' => "2020-01-01 08:00:00",
            'end_at' => "2020-01-02 08:00:00",
            'medical_certificate' => null,
            'comment' => 'Sick kid'
        ]);

        $this->assertCount(0, $absentUser->absences);
        $this->assertCount(1, $actingUser->absences);
    }

    /** @test **/
    public function notProvidingUserExternalIdCreatesAbsenceForAuthenticatedUser()
    {
        $response = $this->json('POST', route('absence.store'), [
            'reason' => 'Sick',
            'start_at' => "2020-01-01 08:00:00",
            'end_at' => "2020-01-02 08:00:00",
            'medical_certificate' => null,
            'comment' => 'Sick kid'
        ]);

        $this->assertNotNull(\Session::all()["flash_message"]);
        $this->assertCount(1, auth()->user()->absences);
    }
}
