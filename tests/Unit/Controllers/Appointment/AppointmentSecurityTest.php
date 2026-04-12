<?php

namespace Tests\Unit\Controllers\Appointment;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Appointment;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Enums\PermissionName;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

#[Group('security')]
#[Group('appointment-controller')]
class AppointmentSecurityTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected $appointment;

    protected $unauthorizedUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Freeze time for deterministic tests
        Carbon::setTestNow('2024-01-15 12:00:00');

        // Create and authenticate a user with default role
        $this->user = User::factory()->withRole('employee')->create();
        $this->actingAs($this->user);

        $this->appointment = Appointment::factory()->create([
            'user_id' => $this->user->id,
            'start_at' => Carbon::now(),
            'end_at' => Carbon::now()->addHour(),
        ]);

        // Create a user without appointment-update permission
        $this->unauthorizedUser = User::factory()->withRole('employee')->create();

        // Disable CSRF middleware for all tests
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    # region happy_path

    #[Test]
    public function it_authorized_user_can_update_appointment()
    {
        /** Arrange */
        $this->withPermissions(PermissionName::APPOINTMENT_EDIT);
        $expectedStart = Carbon::now()->addDay();
        $expectedEnd = Carbon::now()->addDay()->addHour();

        /** Act */
        $response = $this->withSession(['_token' => csrf_token()])->json('POST', route('appointments.update', $this->appointment->external_id), [
            'id' => $this->appointment->id,
            'start' => $expectedStart->toISOString(),
            'end' => $expectedEnd->toISOString(),
            'group' => $this->unauthorizedUser->external_id,
            '_token' => csrf_token(),
        ]);

        /** Assert */
        $response->assertStatus(200);

        $this->appointment->refresh();

        $this->assertSame($expectedStart->toISOString(), $this->appointment->start_at->toISOString());
        $this->assertSame($expectedEnd->toISOString(), $this->appointment->end_at->toISOString());
        $this->assertSame($this->unauthorizedUser->id, $this->appointment->user_id);
    }

    #[Test]
    public function it_authorized_user_can_delete_appointment()
    {
        /** Arrange */
        $this->withPermissions(PermissionName::APPOINTMENT_DELETE);

        /** Act */
        $response = $this->withSession(['_token' => csrf_token()])->json('DELETE', route('appointments.destroy', $this->appointment->external_id), [
            '_token' => csrf_token(),
        ]);

        /** Assert */
        $response->assertStatus(200);
        $this->assertSoftDeleted('appointments', ['id' => $this->appointment->id]);
    }

    # endregion

    # region failure_path

    #[Test]
    public function it_unauthorized_user_cannot_update_appointment()
    {
        /** Arrange */
        $this->actingAs($this->unauthorizedUser);

        /** Act */
        $response = $this->json('POST', route('appointments.update', $this->appointment->external_id), [
            'start' => Carbon::now()->addDay()->toISOString(),
            'end' => Carbon::now()->addDay()->addHour()->toISOString(),
            'group' => $this->user->external_id,
        ]);

        /** Assert */
        $response->assertStatus(403);
    }

    #[Test]
    public function it_appointment_update_requires_permission_check()
    {
        /** Arrange */
        $this->user->roles()->detach();
        $this->user = User::factory()->withRole('employee')->create();
        $this->actingAs($this->user);

        /** Act */
        $response = $this->withSession(['_token' => csrf_token()])->json('POST', route('appointments.update', $this->appointment->external_id), [
            'id' => $this->appointment->id,
            'start' => Carbon::now()->addDay()->toISOString(),
            'end' => Carbon::now()->addDay()->addHour()->toISOString(),
            'group' => $this->user->external_id,
            '_token' => csrf_token(),
        ]);

        /** Assert */
        $response->assertStatus(403);
    }

    #[Test]
    public function it_unauthorized_user_cannot_delete_appointment()
    {
        /** Arrange */
        $this->actingAs($this->unauthorizedUser);

        /** Act */
        $response = $this->withSession(['_token' => csrf_token()])->json('DELETE', route('appointments.destroy', $this->appointment->external_id), [
            '_token' => csrf_token(),
        ]);

        /** Assert */
        $response->assertStatus(403);
    }

    # endregion
}
