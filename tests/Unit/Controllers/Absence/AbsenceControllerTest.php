<?php

namespace Tests\Unit\Controllers\Absence;

use App\Enums\PermissionName;
use App\Models\Permission;
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
        // Step A: Define the User.
        $this->user = User::factory()->withRole('employee')->create();

        // Step B: Call withPermissions.
        $this->withPermissions(PermissionName::ABSENCE_MANAGE);

        // Assert permission is active for this user
        $this->assertTrue($this->user->can(PermissionName::ABSENCE_MANAGE->value), 'User should have absence-manage permission');

        // Step C: Create the Resource (or perform action)
        $user = User::factory()->create();
        $response = $this->json('POST', route('absence.store'), [
            'reason' => 'Sick',
            'user_external_id' => $user->external_id,
            'start_date' => '2020-01-01',
            'end_date' => '2020-01-02',
            'medical_certificate' => null,
            'comment' => 'Sick kid',
        ]);
        $response->assertStatus(302); // or 200, depending on redirect/response

        // Refresh the user to get updated absences relationship
        $absences = $user->fresh()->absences;
        $this->assertNotNull(\Session::all()['flash_message']);
        $this->assertCount(1, $absences);
    }

    #[Test]
    #[Group('junie_repaired')]
    public function creating_absence_for_other_users_without_permission_creates_for_user_it_self()
    {
        // Step A: Define the User.
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        // Step B: Call withPermissions (none needed here but follow order)

        // Step C: Create the Resource (or perform action)
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
        $this->assertCount(1, $this->user->absences);
    }

    #[Test]
    #[Group('junie_repaired')]
    public function not_providing_user_external_id_creates_absence_for_authenticated_user()
    {
        // $this->user is already defined and acting as in setUp
        $response = $this->json('POST', route('absence.store'), [
            'reason' => 'Sick',
            'start_date' => '2020-01-01',
            'end_date' => '2020-01-02',
            'medical_certificate' => null,
            'comment' => 'Sick kid',
        ]);

        $this->assertNotNull(Session::all()['flash_message']);
        $this->assertCount(1, $this->user->absences);
    }
}
