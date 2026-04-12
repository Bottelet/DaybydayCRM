<?php

namespace Tests\Unit\Controllers\Appointment;

use App\Models\Appointment;
use App\Models\User;
use App\Enums\PermissionName;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Regression tests for the timezone conversion removal in AppointmentsController::update().
 *
 * Before the fix, times were converted to Europe/Copenhagen timezone via setTimezone(),
 * which shifted UTC timestamps by +1 or +2 hours depending on DST.
 * After the fix, Carbon::parse() is used directly without any timezone conversion,
 * so the stored times match exactly what was submitted.
 */
#[Group('appointments')]
#[Group('regression')]
class AppointmentTimezoneTest extends AbstractTestCase
{
    use RefreshDatabase;

    private Appointment $appointment;

    protected function setUp(): void
    {
        parent::setUp();

        // Freeze time for deterministic tests
        Carbon::setTestNow('2024-01-15 12:00:00');

        $this->withPermissions([
            PermissionName::APPOINTMENT_EDIT,
            PermissionName::APPOINTMENT_DELETE,
        ]);

        $this->appointment = Appointment::factory()->create([
            'user_id' => $this->user->id,
            'start_at' => Carbon::now(),
            'end_at' => Carbon::now()->addHour(),
            'source_id' => $this->user->id,
            'source_type' => User::class,
            'title' => 'Test appointment',
            'color' => '#FFFFFF',
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    // region happy_path

    #[Test]
    public function it_stores_appointment_start_time_without_timezone_shift()
    {
        /** Arrange */
        // Submit a UTC ISO string (e.g., "2024-03-15T10:00:00.000000Z")
        // Before fix: setTimezone('Europe/Copenhagen') would shift this to 11:00 (UTC+1) or 12:00 (UTC+2 DST)
        // After fix: the stored value should match the submitted value exactly
        $submittedStart = '2024-03-15T10:00:00.000000Z';
        $submittedEnd = '2024-03-15T11:00:00.000000Z';

        /** Act */
        $response = $this->withSession(['_token' => csrf_token()])->json(
            'POST',
            route('appointments.update', $this->appointment->external_id),
            [
                'id' => $this->appointment->id,
                'start' => $submittedStart,
                'end' => $submittedEnd,
                'group' => $this->user->external_id,
                '_token' => csrf_token(),
            ]
        );

        /** Assert */
        $response->assertStatus(200);

        $updated = $this->appointment->fresh();
        $expectedStart = Carbon::parse($submittedStart);
        $expectedEnd = Carbon::parse($submittedEnd);

        // The stored start_at should match the submitted UTC time, NOT a timezone-shifted version
        $this->assertEquals(
            $expectedStart->toISOString(),
            $updated->start_at->toISOString(),
            'start_at should not be shifted by timezone conversion'
        );
        $this->assertEquals(
            $expectedEnd->toISOString(),
            $updated->end_at->toISOString(),
            'end_at should not be shifted by timezone conversion'
        );
    }

    #[Test]
    public function it_stores_appointment_times_matching_submitted_iso_string()
    {
        /** Arrange */
        $newAssignee = User::factory()->create();
        // Winter time: UTC+1 in Copenhagen. Before fix, 09:00Z would become 10:00 local.
        $submittedStart = '2024-01-20T09:00:00.000000Z';
        $submittedEnd = '2024-01-20T09:30:00.000000Z';

        /** Act */
        $response = $this->withSession(['_token' => csrf_token()])->json(
            'POST',
            route('appointments.update', $this->appointment->external_id),
            [
                'id' => $this->appointment->id,
                'start' => $submittedStart,
                'end' => $submittedEnd,
                'group' => $newAssignee->external_id,
                '_token' => csrf_token(),
            ]
        );

        /** Assert */
        $response->assertStatus(200);

        $updated = $this->appointment->fresh();

        // Verify exact ISO string match - no +1 hour offset applied
        $this->assertEquals(
            Carbon::parse($submittedStart)->toISOString(),
            $updated->start_at->toISOString()
        );
        $this->assertEquals(
            Carbon::parse($submittedEnd)->toISOString(),
            $updated->end_at->toISOString()
        );
        // Verify the user was also updated
        $this->assertEquals($newAssignee->id, $updated->user_id);
    }

    // endregion

    // region regression

    #[Test]
    #[Group('regression')]
    public function it_does_not_apply_copenhagen_timezone_shift_to_start_time()
    {
        /** Arrange */
        // Copenhagen is UTC+1 in winter. The old code would call setTimezone('Europe/Copenhagen')
        // which shifts UTC times forward. We verify the hours match exactly.
        $inputHour = 14; // 14:00 UTC
        $submittedStart = sprintf('2024-02-10T%02d:00:00.000000Z', $inputHour);
        $submittedEnd = sprintf('2024-02-10T%02d:00:00.000000Z', $inputHour + 1);

        /** Act */
        $response = $this->withSession(['_token' => csrf_token()])->json(
            'POST',
            route('appointments.update', $this->appointment->external_id),
            [
                'id' => $this->appointment->id,
                'start' => $submittedStart,
                'end' => $submittedEnd,
                'group' => $this->user->external_id,
                '_token' => csrf_token(),
            ]
        );

        /** Assert */
        $response->assertStatus(200);

        $updated = $this->appointment->fresh();

        // The stored hour should be 14, not 15 (which it would be if Copenhagen timezone was applied)
        $this->assertEquals(
            $inputHour,
            (int) $updated->start_at->format('G'),
            'start_at hour should not be offset by Copenhagen timezone (+1 or +2)'
        );
    }

    #[Test]
    #[Group('regression')]
    public function it_does_not_apply_copenhagen_timezone_shift_to_end_time()
    {
        /** Arrange */
        $inputHour = 16; // 16:00 UTC
        $submittedStart = sprintf('2024-07-10T%02d:00:00.000000Z', $inputHour);
        $submittedEnd = sprintf('2024-07-10T%02d:30:00.000000Z', $inputHour);

        /** Act */
        $response = $this->withSession(['_token' => csrf_token()])->json(
            'POST',
            route('appointments.update', $this->appointment->external_id),
            [
                'id' => $this->appointment->id,
                'start' => $submittedStart,
                'end' => $submittedEnd,
                'group' => $this->user->external_id,
                '_token' => csrf_token(),
            ]
        );

        /** Assert */
        $response->assertStatus(200);

        $updated = $this->appointment->fresh();

        // Summer time in Copenhagen is UTC+2. Without the fix, 16:00Z would become 18:00 local.
        // With the fix, stored value should match submitted value: hour 16.
        $this->assertEquals(
            $inputHour,
            (int) $updated->end_at->format('G'),
            'end_at hour should not be offset by Copenhagen timezone (+2 in summer DST)'
        );
    }

    // endregion
}