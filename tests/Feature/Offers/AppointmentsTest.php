<?php

namespace Tests\Feature\Offers;

use App\Enums\PermissionName;
use App\Http\Controllers\AppointmentsController;
use App\Http\Middleware\VerifyCsrfToken;
use App\Http\Requests\Appointment\CreateAppointmentCalendarRequest;
use App\Models\Appointment;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use Tests\AbstractTestCase;

#[CoversClass(AppointmentsController::class)]
class AppointmentsTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected $appointmentsWithInTime;

    protected $appointmentsWithToLate;

    protected $appointmentsWithToEarly;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2024-01-15 12:00:00');

        $this->user = User::factory()->create();
        $role       = Role::query()->firstOrCreate(['name' => 'employee'], ['display_name' => 'Employee']);
        $this->user->attachRole($role);

        $this->withPermissions([
            PermissionName::APPOINTMENT_EDIT,
            PermissionName::APPOINTMENT_DELETE,
        ]);

        $this->appointmentsWithInTime = Appointment::factory()->create([
            'user_id'     => $this->user->id,
            'start_at'    => Carbon::now(),
            'end_at'      => Carbon::now()->addHour(),
            'source_id'   => $this->user->id,
            'source_type' => User::class,
            'title'       => 'test',
            'color'       => '#FFFFFF',
        ]);

        $this->appointmentsWithToLate = Appointment::factory()->create([
            'user_id'     => $this->user->id,
            'start_at'    => Carbon::now()->addWeeks(6),
            'end_at'      => Carbon::now()->addWeeks(6)->addHour(),
            'source_id'   => $this->user->id,
            'source_type' => User::class,
            'title'       => 'test',
            'color'       => '#FFFFFF',
        ]);
        $this->appointmentsWithToEarly = Appointment::factory()->create([
            'user_id'     => $this->user->id,
            'start_at'    => Carbon::now()->subWeeks(4),
            'end_at'      => Carbon::now()->subWeeks(4)->addHour(),
            'source_id'   => $this->user->id,
            'source_type' => User::class,
            'title'       => 'test',
            'color'       => '#FFFFFF',
        ]);

        $this->appointment = Appointment::factory()->create([
            'user_id'  => $this->user->id,
            'start_at' => Carbon::now(),
            'end_at'   => Carbon::now()->addHour(),
        ]);

        $this->actingAs($this->user);

        $roleUnauthorized = Role::query()->firstOrCreate(['name' => 'unauthorized'], ['display_name' => 'Unauthorized']);
        $this->unauthorizedUser = User::factory()->create();
        $this->unauthorizedUser->attachRole($roleUnauthorized);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    #[Test]
    public function it_verifies_appointments_controller_does_not_have_create_request_dependency()
    {
        /* Arrange */
        $reflector = new ReflectionClass(AppointmentsController::class);

        /* Act */
        $methods     = $reflector->getMethods(ReflectionMethod::IS_PUBLIC);
        $methodNames = array_map(fn ($m) => $m->getName(), $methods);

        /* Assert */
        $this->assertNotContains('store', $methodNames);
    }

    #[Test]
    public function it_can_update_appointment_times()
    {
        /* Arrange */
        $this->withPermissions(PermissionName::APPOINTMENT_EDIT);
        $appointment = Appointment::factory()->create([
            'user_id'     => $this->user->id,
            'start_at'    => Carbon::now(),
            'end_at'      => Carbon::now()->addHour(),
            'source_id'   => $this->user->id,
            'source_type' => User::class,
            'title'       => 'test',
            'color'       => '#FFFFFF',
        ]);
        $newAssignee = User::factory()->create();

        /* Act */
        $response = $this->withSession(['_token' => csrf_token()])->post(route('appointments.update', $appointment->external_id), [
            'id'     => $appointment->id,
            'start'  => Carbon::now()->addDay()->toISOString(),
            'end'    => Carbon::now()->addDay()->addHour()->toISOString(),
            'group'  => $newAssignee->external_id,
            '_token' => csrf_token(),
        ]);

        /* Assert */
        $response->assertSuccessful();
        $updatedAppointment = $appointment->fresh();
        $this->assertEquals($newAssignee->id, $updatedAppointment->user_id);
    }

    #[Test]
    public function it_returns_json_error_when_appointment_update_fails()
    {
        /* Arrange */
        $this->withPermissions(PermissionName::APPOINTMENT_EDIT);
        $appointment = Appointment::factory()->create([
            'user_id'     => $this->user->id,
            'start_at'    => Carbon::now(),
            'end_at'      => Carbon::now()->addHour(),
            'source_id'   => $this->user->id,
            'source_type' => User::class,
            'title'       => 'test',
            'color'       => '#FFFFFF',
        ]);

        /* Act */
        $response = $this->postJson(route('appointments.update', $appointment->external_id), [
            'id'    => $appointment->id,
            'start' => Carbon::now()->addDay()->toISOString(),
            'end'   => Carbon::now()->addDay()->addHour()->toISOString(),
            'group' => 'does-not-exist',
        ]);

        /* Assert */
        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'group',
        ]);
    }

    #[Test]
    public function it_authorized_user_can_update_appointment()
    {
        /* Arrange */
        $this->withPermissions(PermissionName::APPOINTMENT_EDIT);
        $expectedStart = Carbon::now()->addDay();
        $expectedEnd   = Carbon::now()->addDay()->addHour();

        /* Act */
        $response = $this->withSession(['_token' => csrf_token()])->post(route('appointments.update', $this->appointment->external_id), [
            'id'     => $this->appointment->id,
            'start'  => $expectedStart->toISOString(),
            'end'    => $expectedEnd->toISOString(),
            'group'  => $this->unauthorizedUser->external_id,
            '_token' => csrf_token(),
        ]);

        /* Assert */
        $response->assertStatus(200);

        $this->appointment->refresh();

        $this->assertSame($expectedStart->toISOString(), $this->appointment->start_at->toISOString());
        $this->assertSame($expectedEnd->toISOString(), $this->appointment->end_at->toISOString());
        $this->assertSame($this->unauthorizedUser->id, $this->appointment->user_id);
    }

    #[Test]
    public function it_unauthorized_user_cannot_update_appointment()
    {
        /* Arrange */
        $this->actingAs($this->unauthorizedUser);

        /* Act */
        $response = $this->withSession(['_token' => csrf_token()])->post(route('appointments.update', $this->appointment->external_id), [
            'start'  => Carbon::now()->addDay()->toISOString(),
            'end'    => Carbon::now()->addDay()->addHour()->toISOString(),
            'group'  => $this->user->external_id,
            '_token' => csrf_token(),
        ]);

        /* Assert */
        $response->assertStatus(403);
    }

    #[Test]
    public function it_requires_permission_check_for_appointment_update()
    {
        /* Arrange */
        $role = Role::query()->firstOrCreate(['name' => 'no-perms'], ['display_name' => 'No Perms']);
        $this->user = User::factory()->create();
        $this->user->attachRole($role);
        $this->actingAs($this->user);

        /* Act */
        $response = $this->withSession(['_token' => csrf_token()])->post(route('appointments.update', $this->appointment->external_id), [
            'id'     => $this->appointment->id,
            'start'  => Carbon::now()->addDay()->toISOString(),
            'end'    => Carbon::now()->addDay()->addHour()->toISOString(),
            'group'  => $this->user->external_id,
            '_token' => csrf_token(),
        ]);

        /* Assert */
        $response->assertStatus(403);
    }

    #[Test]
    public function it_verifies_appointments_controller_retains_update_method()
    {
        /* Arrange */

        /* Act & Assert */
        $this->assertTrue(
            method_exists(AppointmentsController::class, 'update'),
            'AppointmentsController::update() should still exist'
        );
    }

    #[Test]
    public function it_authorized_user_can_delete_appointment()
    {
        /* Arrange */
        $this->withPermissions(PermissionName::APPOINTMENT_DELETE);

        /* Act */
        $response = $this->withSession(['_token' => csrf_token()])->delete(route('appointments.destroy', $this->appointment->external_id), [
            '_token' => csrf_token(),
        ]);

        /* Assert */
        $response->assertStatus(200);
        $this->assertSoftDeleted('appointments', ['id' => $this->appointment->id]);
    }

    #[Test]
    public function it_can_destroy_appointment()
    {
        /* Arrange */
        $this->withPermissions(PermissionName::APPOINTMENT_DELETE);
        $appointment = Appointment::factory()->create([
            'user_id'     => $this->user->id,
            'start_at'    => Carbon::now(),
            'end_at'      => Carbon::now()->addHour(),
            'source_id'   => $this->user->id,
            'source_type' => User::class,
            'title'       => 'test',
            'color'       => '#FFFFFF',
        ]);
        $appointmentExternalId = $appointment->external_id;

        /* Act */
        $response = $this->withSession(['_token' => csrf_token()])->delete(route('appointments.destroy', $appointmentExternalId), [
            '_token' => csrf_token(),
        ]);

        /* Assert */
        $response->assertSuccessful();
        $this->assertNull(Appointment::whereExternalId($appointmentExternalId)->first());
    }

    #[Test]
    public function it_unauthorized_user_cannot_delete_appointment()
    {
        /* Arrange */
        $this->actingAs($this->unauthorizedUser);

        /* Act */
        $response = $this->withSession(['_token' => csrf_token()])->delete(route('appointments.destroy', $this->appointment->external_id), [
            '_token' => csrf_token(),
        ]);

        /* Assert */
        $response->assertStatus(403);
    }

    #[Test]
    public function it_verifies_appointments_controller_retains_destroy_method()
    {
        /* Arrange */

        /* Act & Assert */
        $this->assertTrue(
            method_exists(AppointmentsController::class, 'destroy'),
            'AppointmentsController::destroy() should still exist'
        );
    }

    #[Test]
    public function it_creates_appointment_calendar_request_class_no_longer_used_by_controller()
    {
        /* Arrange */
        $reflector = new ReflectionClass(AppointmentsController::class);
        $methods   = $reflector->getMethods(ReflectionMethod::IS_PUBLIC);

        /* Act & Assert */
        foreach ($methods as $method) {
            $params = $method->getParameters();
            foreach ($params as $param) {
                $type = $param->getType();
                if ($type && ! $type->isBuiltin()) {
                    $typeName = $type instanceof ReflectionNamedType ? $type->getName() : (string) $type;
                    $this->assertNotEquals(
                        CreateAppointmentCalendarRequest::class,
                        $typeName,
                        'CreateAppointmentCalendarRequest should not be used in any controller method'
                    );
                }
            }
        }
    }

    #[Test]
    public function it_verifies_appointments_controller_does_not_have_store_method()
    {
        /* Arrange */

        /* Act & Assert */
        $this->assertFalse(
            method_exists(AppointmentsController::class, 'store'),
            'AppointmentsController::store() should have been removed'
        );
    }

    #[Test]
    public function it_can_get_appointments_within_time_slot()
    {
        /* Arrange */
        $correctAppointment = null;

        /* Act */
        $r = $this->get('/appointments/data');

        /* Assert */
        foreach ($r->json() as $appointment) {
            $this->assertNotTrue($appointment['external_id'] == $this->appointmentsWithToLate->external_id);
            $this->assertNotTrue($appointment['external_id'] == $this->appointmentsWithToEarly->external_id);
            if ($appointment['external_id'] == $this->appointmentsWithInTime->external_id) {
                $correctAppointment = $appointment;
            }
        }

        $this->assertEquals($this->appointmentsWithInTime->start_at->toISOString(), $correctAppointment['start_at']);
        $this->assertEquals($this->appointmentsWithInTime->end_at->toISOString(), $correctAppointment['end_at']);
        $this->assertCount(3, User::whereExternalId($this->user->external_id)->first()->appointments);
    }

    #[Test]
    #[Group('regression')]
    public function it_returns_user_appointments_via_morph_relationship()
    {
        /* Arrange */

        /* Act */
        $appointments = $this->user->appointments;

        /* Assert */
        $this->assertCount(3, $appointments);
        $externalIds = $appointments->pluck('external_id')->toArray();
        $this->assertContains($this->appointmentsWithInTime->external_id, $externalIds);
        $this->assertContains($this->appointmentsWithToLate->external_id, $externalIds);
        $this->assertContains($this->appointmentsWithToEarly->external_id, $externalIds);
    }

    #[Test]
    #[Group('regression')]
    public function it_does_not_return_appointments_for_other_source_types_in_user_appointments_morph()
    {
        /* Arrange */
        $otherUser        = User::factory()->create();
        $otherAppointment = Appointment::factory()->create([
            'user_id'     => $this->user->id,
            'source_id'   => $otherUser->id,
            'source_type' => User::class,
            'title'       => 'other source',
            'color'       => '#000000',
        ]);

        /* Act */
        $appointments          = $this->user->appointments;
        $otherUserAppointments = $otherUser->appointments;

        /* Assert */
        $otherIds = $appointments->pluck('external_id')->toArray();
        $this->assertNotContains($otherAppointment->external_id, $otherIds);
        $this->assertContains($otherAppointment->external_id, $otherUserAppointments->pluck('external_id')->toArray());
    }
}
