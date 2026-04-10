<?php

namespace Tests\Unit\Controllers\Absence;

use App\Models\User;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;

class AbsenceControllerTest extends AbstractTestCase
{
    use RefreshDatabase;

    #[Test]
    #[Group('junie_repaired')]
    public function can_create_absence_for_other_user()
    {
        // Create authenticated user with absence-manage permission
        $authUser = User::factory()->withRole('employee')->create();
        $managePermission = \App\Models\Permission::firstOrCreate(['name' => 'absence-manage']);
        $authUser->roles->first()->attachPermission($managePermission);
        $this->actingAs($authUser);

        $user = User::factory()->create();
        $response = $this->json('POST', route('absence.store'), [
            'reason' => 'Sick',
            'user_external_id' => $user->external_id,
            'start_date' => '2020-01-01',
            'end_date' => '2020-01-02',
            'medical_certificate' => null,
            'comment' => 'Sick kid',
        ]);

        $absences = $user->absences;
        $this->assertNotNull(Session::all()['flash_message']);
        $this->assertCount(1, $absences);
    }

    #[Test]
    #[Group('junie_repaired')]
    public function creating_absence_for_other_users_without_permission_creates_for_user_it_self()
    {

        $actingUser = User::factory()->create();
        $this->actingAs($actingUser);

        $absentUser = User::factory()->create();
        $response = $this->json('POST', route('absence.store'), [
            'reason' => 'Sick',
            'user_external_id' => $absentUser->external_id,
            'start_date' => '2020-01-01',
            'end_date' => '2020-01-02',
            'medical_certificate' => null,
            'comment' => 'Sick kid',
        ]);

        $this->assertCount(0, $absentUser->absences);
        $this->assertCount(1, $actingUser->absences);
    }

    #[Test]
    #[Group('junie_repaired')]
    public function not_providing_user_external_id_creates_absence_for_authenticated_user()
    {

        $response = $this->json('POST', route('absence.store'), [
            'reason' => 'Sick',
            'start_date' => '2020-01-01',
            'end_date' => '2020-01-02',
            'medical_certificate' => null,
            'comment' => 'Sick kid',
        ]);

        $this->assertNotNull(Session::all()['flash_message']);
        $this->assertCount(1, auth()->user()->absences);
    }
}
